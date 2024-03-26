<?php

namespace App\Http\Controllers;

use App\Helpers\FileHelper;
use App\Models\User;
use App\Models\Driver;
use App\Models\EmailVerifycation;
use App\Models\Message;
use App\Models\Profile;
use App\Models\MessageFile;
use App\Models\UserDevice;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\EmailHelper;
use Throwable;
use Carbon\Carbon;
use function Composer\Autoload\includeFile;

class UserController extends Controller
{
    public function login(Request $request): array
    {
        $result = [
            'status' => false,
            'message' => 'Invalid login details!',
            'token' => '',
            'user' => []
        ];

        $user = User::where('email', $request->email)->without('profile')->first();

        if ($user && empty($user->email_verified_at)) {
            $result['message'] = 'Email not verified!';
            return $result;
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
            // Аутентификация успешна...
            $result['status'] = true;
            $result['message'] = 'Welcome';
            $result['token'] = Auth::user()->remember_token;
            if ($user->role_id === 5) { // Если водитель, берем данные с drivers а не с profiles
                $user->profile = Driver::where('user_id', $user->id)->first();
            } else {
                $user->profile = Profile::where('user_id', $user->id)->first();
            }
            $result['user'] = $user;
        }

        return $result;
    }

    public function auth(Request $request): array
    {
        $result = [
            'status' => false,
            'user' => []
        ];
        $user = User::where('remember_token', $request->token)->without('profile')->first();

        if (!empty($request->token) && $user) {
            $result['status'] = true;
            if ($user->role_id === 5) { // Если водитель, берем данные с drivers а не с profiles
                $user->profile = Driver::where('user_id', $user->id)->first();
            } else {
                $user->profile = Profile::where('user_id', $user->id)->first();
            }
            $result['user'] = $user;
        }

        return $result;
    }

    public function verify(Request $request): \Exception|array
    {
        $result = [
            'status' => false,
            'message' => 'Incorrect email, please use the same email that was used for registration.',
            'user' => []
        ];

        $user = User::where('email', $request->email)->first();

        if (!$user)
        {
            return $result;
        }

        if (!empty($user->email_verified_at)) {
            $result['message'] = 'The user with this email has already been verified!';
            return  $result;
        }

        try {
            $user->update([
                'password' => Hash::make($request->password),
                'email_verified_at' => date('Y-m-d H:i:s')
            ]);

            if (Auth::attempt(['email' => $request->email, 'password' => $request->password], true)) {
                // Аутентификация успешна...
                $result['status'] = true;
                $result['message'] = 'Success!';
                $result['token'] = Auth::user()->remember_token;
                $result['user'] = $user;
            }
        } catch (\Exception $exception) {
            return $exception;
        }

        return $result;
    }

    public function checkEmail(Request $request)
    {
        $result = [
          'status' => false,
          'email' => []
        ];

        $user = User::where('email', $request->email)->first();

        if ($user) {
            $result['status'] = true;
            $result['email'] = $user->email;
        } else {
            $result['message'] = 'The specified email was not found.';
        }

        return $result;
    }

    public function updatePassword(Request $request): array
    {
        $result = [
            'status' => false,
            'message' => 'Failed to change the password for the specified user.'
        ];

        $user = User::where('email', $request->email);

        if (!empty($request->checkStatusCode)) {
            $emailVerify = EmailVerifycation::where('email', $request->email)->first();

            if (!$emailVerify->status) {
                $result['message'] = 'The code invalid.';
                return $result;
            }
        }

        if ($user && $request->password) {
            $user->update([
                'password' => Hash::make($request->password),
            ]);

            $result['status'] = true;
            $result['message'] = 'Success!';
        }

        return $result;
    }

    public function forgotPassword(Request $request): array
    {
        $result = [
            'status' => false
        ];
        $code = rand(1000, 9999);
        $checkEmail = $this->checkEmail($request);

        if (!$checkEmail['status']) {
            $result['message'] = $checkEmail['message'];
            return $result;
        }

        try {
            $emailVerify = EmailVerifycation::where('email', $request->email)->first();

            if (!empty($emailVerify)) {
                $emailVerify->update([
                    'code' => $code
                ]);
            } else {
                EmailVerifycation::create([
                    'email' => $request->email,
                    'code' => $code,
                    'status' => 0
                ]);
            }

            EmailHelper::sendEmailFromForgotPassword($code, $request->email);

            $result['status'] = true;
        } catch (\Exception $exception) {
            $result['status'] = false;
            $result['message'] = 'Error - ' . $exception;
        }

        return $result;
    }

    /**
     * @throws \Exception
     */
    public function checkCode(Request $request): array
    {
        $result = [
            'status' => false
        ];

        $emailVerify = EmailVerifycation::where('email', $request->email)->first();
        $now = new \DateTime('now');
        $emailVerifyMinTTL = $now->diff(new \DateTime($emailVerify->updated_at))->i;

        if (empty($emailVerify)) {
            $result['message'] = 'The specified email was not found.';
            return $result;
        }

        if ($request->code !== $emailVerify->code) {
            $result['message'] = 'The code invalid.';
            return $result;
        }

        if ($emailVerifyMinTTL > env('CODE_VERIFY_MIN_TTL', '5')) {
            $result['message'] = 'The lifetime of the confirmation code has expired.';
            return $result;
        }

        $emailVerify->update([
            'status' => 1
        ]);

        $result['status'] = true;

        return $result;
    }


    public function getMessages(Request $request)
    {
        if (!$firstChatByUser = Message::where('user_id', $request->user_id)->first()) {
            return [];
        }

        if (User::find($request->user_id)->is_admin) {
            $messagesInChat = Message::where('chat_id', $firstChatByUser->chat_id)->get();
        } else { // Если не админ то последние 24 часа
            $messagesInChat = Message::where('chat_id', $firstChatByUser->chat_id)->whereDate('created_at', '>=', Carbon::now()->subDay())->get();
        }

        $result = [
            'messages' => [],
            'chat_id' => $firstChatByUser->chat_id,
        ];

        foreach ($messagesInChat as $message) {
            $result['messages'][] = [
                'author' => ($message->user_id !== $request->user_id) ? 'admin' : 'user',
                'message' => $message->message,
                'date' => $message->created_at,
                'status' => $message->status,
                'files' => $message->files
            ];
            $result['chat_id'] = $message->chat_id;
        }

        return $result;
    }

    public function addMessage(Request $request)
    {
        date_default_timezone_set('UTC');
        ini_set('post_max_size', '64M');
        ini_set('upload_max_filesize', '64M');
        try {
            DB::beginTransaction();

            $message = Message::create([
                'chat_id' => $request->chat_id ?? Message::max('chat_id') + 1,
                'user_id' => $request->user_id,
                'subject' => '',
                'message' => $request->message,
                'status' => 0,
            ]);

            if (!empty($request->file('file'))) {
                $filename = pathinfo($request->file('file')->getClientOriginalName(), PATHINFO_FILENAME);
                $path = $message->id . '/' . $filename;
                $file = FileHelper::upload($request->file('file'), $path, 'messages', $_ENV['APP_IMAGE_CHAT_WIDTH'], $_ENV['APP_IMAGE_CHAT_HEIGHT'], $_ENV['APP_IMAGE_CHAT_EXTENSION']);

                MessageFile::create([
                    'message_id' => $message->id,
                    'filename' => $file['filename'],
                ]);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            return json_encode([
                'status' => false,
                'error' => $e->getMessage()
            ]);
        }

        return json_encode([
            'status' => true,
            'message' => $request->message,
            'date' => $message->created_at
        ]);
    }

    public function addDevice(Request $request)
    {
        return UserDevice::create([
            'user_id' => $request->user_id,
            'token' => $request->token
        ]);
    }

    public function updateDevice(Request $request)
    {
        return UserDevice::find($request->id)->update([
            'token' => $request->token
        ]);
    }

    public function updateStatus(Request $request)
    {
        return User::find($request->user_id)->update([
            'status' => $request->status
        ]);
    }
    
    public function getCustomers()
    {
        $customers = User::where('role_id', 3)->get();
        foreach ($customers as $customer) {
            $customer->orderCount = $customer->getOrdersCount();
        }

        return $customers;
    }

    public function getOneCustomer(Request $request)
    {
        return User::find($request->id);
    }

    public function addCustomer(Request $request)
    {
        try {
            DB::beginTransaction();

            $userData = $request->validate([
                'first_name' => 'required|max:255',
                'last_name'  => 'required|max:255',
                'email'      => 'required|email',
                'phone'      => 'required',
                'day_of_birth' => 'required|date',
            ]);
            $userId                   = User::max('id') + 1;
            $userData['password']     = Hash::make('Pass!'. rand(100, 900));
            $userData['role_id']      = 3;
            $userData['id']           = $userId;
            $dayOfBirthUser           = new DateTime($userData['day_of_birth']);
            $userData['day_of_birth'] = $dayOfBirthUser->format('Y-m-d H:i:s');

            $profileData = $request->validate([
                'first_name' => 'required|max:255',
                'last_name'  => 'required|max:255',
                'country'    => 'required|max:255',
                //    'gender'     => 'required',
            ]);
            $profileData['user_id'] = $userId;
            $profileData['address'] = '';
            $profileData['postal_code'] = '';

            User::create($userData);
            Profile::create($profileData);

            DB::commit();
            return ['status' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['status' => false, 'error' => 'Fields are not filled in correctly'];
        }
    }

    public function updateCustomer(Request $request)
    {
        //dd($request->toArray());
        try {
            DB::beginTransaction();

            $request->validate([
                'first_name' => 'max:255',
                'last_name'  => 'max:255',
                'email'      => 'email',
                'phone'      => 'max:255',
            //    'day_of_birth' => 'date',
            ]);

            $profileData = $request->validate([
                'first_name' => 'max:255',
                'last_name'  => 'max:255',
                'country'    => 'max:255',
            ]);
            $dayOfBirthUser        = new DateTime($request->day_of_birth);
            $request->day_of_birth = $dayOfBirthUser->format('Y-m-d H:i:s');

            User::find($request->user_id)->update($request->toArray());
            if ($profile = Profile::where('user_id', $request->user_id)->first()) {
                $profile->update($profileData);
            }

            DB::commit();
            return ['status' => true];
        } catch (\Throwable $e) {
            DB::rollBack();
            return ['status' => false, 'error' => 'Fields are not filled in correctly'];
        }
    }

    public function update(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = json_decode($request->data);
            $userId = $data->user_id;

            $userData = [
                'first_name'   => $data->first_name,
                'last_name'    => $data->last_name,
                'email'        => $data->email,
                'phone'        => $data->phone,
                'day_of_birth' => $data->day_of_birth,
            ];

            $profileData = [
                'first_name'   => $data->first_name,
                'last_name'    => $data->last_name,
                'country'      => $data->country,
                'whatsapp'     => $data->whatsapp,
                'address'      => $data->address,
                'postal_code'  => $data->postal_code,
                //    'gender'     => 'required',
            ];

            if (!empty($request->file('driverLicence'))) {
                $driverLicenceFile = FileHelper::upload(
                    $request->file('driverLicence'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'driver-licence'
                );

                $profileData['driver_licence'] = $driverLicenceFile['filename'];
            }

            if (!empty($request->file('criminalCheck'))) {
                $criminalCheckFile = FileHelper::upload(
                    $request->file('criminalCheck'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'criminal-check'
                );

                $profileData['criminal_check'] = $criminalCheckFile['filename'];
            }

            if (!empty($request->file('photo'))) {
                $photoFile = FileHelper::upload(
                    $request->file('photo'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'photo'
                );

                $profileData['photo'] = $photoFile['filename'];
            }

            if (!empty($request->file('passport'))) {
                $passportFile = FileHelper::upload(
                    $request->file('passport'),
                    $userId,
                    'profiles',
                    null,
                    null,
                    null,
                    'passport'
                );

                $profileData['passport'] = $passportFile['filename'];
            }

            User::find($userId)->update($userData);
            Profile::where('user_id', $userId)->update($profileData);

            DB::commit();
            return true;
        } catch (\Throwable $e) {
            DB::rollBack();
            return $e->getMessage();
        }
    }

    public function getPartners()
    {
        return User::select('id')->where('role_id', 5)->with(['profile' => function ($query) {
                $query->without('cities');
                $query->select(['user_id', 'last_name', 'first_name']);
            }])->without(['company', 'device'])->get();
    }
}
