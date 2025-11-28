<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\LoginHistory;
use App\Models\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return array_merge(
            $request->only($this->username(), 'password'),
            ['active_status' => true] // Only allow login for active users
        );
    }

    /**
     * Handle a failed login attempt.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        // Check if user exists but is inactive
        $user = User::where($this->username(), $request->{$this->username()})->first();
        
        if ($user && !$user->active_status) {
            throw ValidationException::withMessages([
                'email' => ['Your account is inactive. Please contact an administrator to activate your account.'],
            ]);
        }

        throw ValidationException::withMessages([
            $this->username() => [trans('auth.failed')],
        ]);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        $this->logLoginHistory($request, $user);
    }

    /**
     * Log the user's login history.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return void
     */
    protected function logLoginHistory(Request $request, $user)
    {
        $userAgent = $request->userAgent();
        $ipAddress = $request->ip();
        $deviceInfo = $this->detectDevice($userAgent);

        // Get location from IP (basic implementation, can be extended with IP geolocation service)
        $location = $this->getLocationFromIp($ipAddress);

        LoginHistory::create([
            'user_id' => $user->id,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'device_name' => $deviceInfo['name'],
            'device_type' => $deviceInfo['type'],
            'city' => $location['city'] ?? null,
            'country' => $location['country'] ?? null,
            'session_id' => $request->session()->getId(),
            'login_at' => now(),
        ]);
    }

    /**
     * Detect device information from user agent.
     *
     * @param  string  $userAgent
     * @return array
     */
    protected function detectDevice($userAgent)
    {
        $deviceName = 'Unknown Device';
        $deviceType = 'desktop';

        // iPhone detection
        if (preg_match('/iPhone/i', $userAgent)) {
            $deviceName = $this->detectiPhoneModel($userAgent);
            $deviceType = 'mobile';
        }
        // iPad detection
        elseif (preg_match('/iPad/i', $userAgent)) {
            $deviceName = $this->detectiPadModel($userAgent);
            $deviceType = 'tablet';
        }
        // Android detection
        elseif (preg_match('/Android/i', $userAgent)) {
            $deviceInfo = $this->detectAndroidDevice($userAgent);
            $deviceName = $deviceInfo['name'];
            $deviceType = $deviceInfo['type'];
        }
        // Windows Phone
        elseif (preg_match('/Windows Phone/i', $userAgent)) {
            $deviceName = 'Windows Phone';
            $deviceType = 'mobile';
        }
        // Mac detection
        elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
            $deviceName = $this->detectMacModel($userAgent);
            $deviceType = 'desktop';
        }
        // Windows detection
        elseif (preg_match('/Windows/i', $userAgent)) {
            $deviceName = $this->detectWindowsDevice($userAgent);
            $deviceType = 'desktop';
        }
        // Linux
        elseif (preg_match('/Linux/i', $userAgent)) {
            $deviceName = $this->detectLinuxDistribution($userAgent);
            $deviceType = 'desktop';
        }
        // Chrome OS
        elseif (preg_match('/CrOS/i', $userAgent)) {
            $deviceName = 'Chrome OS';
            $deviceType = 'desktop';
        }
        // Other Unix
        elseif (preg_match('/X11/i', $userAgent)) {
            $deviceName = 'Unix PC';
            $deviceType = 'desktop';
        }

        return [
            'name' => $deviceName,
            'type' => $deviceType,
        ];
    }

    /**
     * Detect iPhone model from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectiPhoneModel($userAgent)
    {
        // iPhone model identifiers (iOS 15+)
        if (preg_match('/iPhone(\d+),(\d+)/i', $userAgent, $matches)) {
            $model = 'iPhone ' . $matches[1];
            $subModel = $matches[2];
            // Add Pro/Max suffix if available
            if (preg_match('/Pro Max/i', $userAgent)) {
                $model .= ' Pro Max';
            } elseif (preg_match('/Pro/i', $userAgent)) {
                $model .= ' Pro';
            } elseif (preg_match('/Mini/i', $userAgent)) {
                $model .= ' Mini';
            } elseif (preg_match('/Plus/i', $userAgent)) {
                $model .= ' Plus';
            }
            return $model;
        }

        // Older iPhone detection
        if (preg_match('/iPhone\s*OS\s*([\d_]+)/i', $userAgent, $matches)) {
            $osVersion = str_replace('_', '.', $matches[1]);
            return 'iPhone (iOS ' . $osVersion . ')';
        }

        return 'iPhone';
    }

    /**
     * Detect iPad model from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectiPadModel($userAgent)
    {
        // iPad model identifiers
        if (preg_match('/iPad(\d+),(\d+)/i', $userAgent, $matches)) {
            $model = 'iPad';
            // Add model variant if available
            if (preg_match('/Pro/i', $userAgent)) {
                $model .= ' Pro';
            } elseif (preg_match('/Air/i', $userAgent)) {
                $model .= ' Air';
            } elseif (preg_match('/Mini/i', $userAgent)) {
                $model .= ' Mini';
            }
            return $model;
        }

        // iPadOS detection
        if (preg_match('/iPad.*OS\s*([\d_]+)/i', $userAgent, $matches)) {
            $osVersion = str_replace('_', '.', $matches[1]);
            return 'iPad (iPadOS ' . $osVersion . ')';
        }

        return 'iPad';
    }

    /**
     * Detect Android device from user agent.
     *
     * @param  string  $userAgent
     * @return array
     */
    protected function detectAndroidDevice($userAgent)
    {
        $deviceName = 'Android Device';
        $deviceType = 'mobile';

        // Extract device model from user agent
        // Format: Mozilla/5.0 (Linux; Android 13; SM-S908B) AppleWebKit/...
        if (preg_match('/Android\s+[\d.]+[^;]*;\s*([^)]+)\)/i', $userAgent, $matches)) {
            $deviceInfo = trim($matches[1] ?? '');
            
            // Clean up device info
            $deviceInfo = preg_replace('/\s+Build\/[^;]*/i', '', $deviceInfo);
            $deviceInfo = preg_replace('/\s+wv\)$/i', '', $deviceInfo);
            $deviceInfo = trim($deviceInfo);

            // Try to extract meaningful device name
            if (!empty($deviceInfo) && $deviceInfo !== 'wv' && $deviceInfo !== 'Mobile') {
                // Check if it's a known model identifier
                $deviceName = $this->formatAndroidDeviceName($deviceInfo);
            }
        }

        // Check if it's a tablet (Android tablets usually don't have "Mobile" in user agent)
        if (!preg_match('/Mobile/i', $userAgent)) {
            $deviceType = 'tablet';
        }

        return [
            'name' => $deviceName,
            'type' => $deviceType,
        ];
    }

    /**
     * Format Android device name from model identifier.
     *
     * @param  string  $modelIdentifier
     * @return string
     */
    protected function formatAndroidDeviceName($modelIdentifier)
    {
        // Samsung devices
        if (preg_match('/SM-([A-Z0-9]+)/i', $modelIdentifier, $matches)) {
            $model = $matches[1];
            // Map common Samsung models
            $samsungModels = [
                'S918B' => 'Galaxy S23 Ultra',
                'S918N' => 'Galaxy S23 Ultra',
                'S908B' => 'Galaxy S22 Ultra',
                'S908N' => 'Galaxy S22 Ultra',
                'S901B' => 'Galaxy S22',
                'S906B' => 'Galaxy S22+',
                'G991B' => 'Galaxy S21',
                'G996B' => 'Galaxy S21+',
                'G998B' => 'Galaxy S21 Ultra',
                'G981B' => 'Galaxy S20',
                'G986B' => 'Galaxy S20+',
                'G988B' => 'Galaxy S20 Ultra',
                'T970' => 'Galaxy Tab S7',
                'T976B' => 'Galaxy Tab S7+',
                'T875' => 'Galaxy Tab S6',
            ];
            if (isset($samsungModels[$model])) {
                return 'Samsung ' . $samsungModels[$model];
            }
            return 'Samsung Galaxy ' . $model;
        }

        // Google Pixel
        if (preg_match('/(Pixel\s*\d+[a-z]*)/i', $modelIdentifier, $matches)) {
            return 'Google ' . $matches[1];
        }

        // Xiaomi
        if (preg_match('/(Mi\s*\d+|Redmi\s*\w+|POCO\s*\w+)/i', $modelIdentifier, $matches)) {
            return 'Xiaomi ' . $matches[1];
        }

        // OnePlus
        if (preg_match('/(ONEPLUS\s*[A-Z0-9]+|OnePlus\s*\w+)/i', $modelIdentifier, $matches)) {
            return 'OnePlus ' . str_replace('ONEPLUS', '', $matches[1]);
        }

        // Huawei
        if (preg_match('/(LIO|ELS|ANA|NOP|CLT|EML|EVR|MAR|LYA|VOG|ELE|TAH|YAL|JNY|ANA|OXF|ELS|MAR|DVC|NOH|PCT|ANY|ANA|TAH|BKL|DVC|NOP|OXF|ELS|MAR|VOG|LYA|EVR|CLT|NOP|LIO|ELS|ANA|EML|EVR|MAR|LYA|VOG|ELE|TAH|YAL|JNY|ANA|OXF|ELS|MAR|DVC|NOH|PCT|ANY|ANA|TAH|BKL|DVC|NOP|OXF|ELS|MAR|VOG|LYA|EVR|CLT|NOP)/i', $modelIdentifier)) {
            return 'Huawei Device';
        }

        // Return formatted model identifier
        return ucwords(strtolower(str_replace(['_', '-'], ' ', $modelIdentifier)));
    }

    /**
     * Detect Mac model from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectMacModel($userAgent)
    {
        // Extract macOS version
        if (preg_match('/Mac OS X\s*([\d_]+)/i', $userAgent, $matches)) {
            $osVersion = str_replace('_', '.', $matches[1]);
            
            // Try to detect Mac model
            if (preg_match('/Intel Mac|Intel/i', $userAgent)) {
                return 'Mac (Intel, macOS ' . $osVersion . ')';
            } elseif (preg_match('/Apple Silicon|ARM/i', $userAgent)) {
                return 'Mac (Apple Silicon, macOS ' . $osVersion . ')';
            }
            
            return 'Mac (macOS ' . $osVersion . ')';
        }

        return 'Mac';
    }

    /**
     * Detect Windows device from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectWindowsDevice($userAgent)
    {
        // Extract Windows version
        if (preg_match('/Windows NT\s*([\d.]+)/i', $userAgent, $matches)) {
            $windowsVersion = $this->getWindowsVersionName($matches[1]);
            
            // Check for x64 or ARM architecture
            if (preg_match('/ARM64|ARM/i', $userAgent)) {
                return 'Windows ' . $windowsVersion . ' (ARM)';
            } elseif (preg_match('/WOW64|x64/i', $userAgent)) {
                return 'Windows ' . $windowsVersion . ' (64-bit)';
            }
            
            return 'Windows ' . $windowsVersion;
        }

        return 'Windows PC';
    }

    /**
     * Detect Linux distribution from user agent.
     *
     * @param  string  $userAgent
     * @return string
     */
    protected function detectLinuxDistribution($userAgent)
    {
        // Check for specific Linux distributions
        if (preg_match('/Ubuntu/i', $userAgent)) {
            return 'Ubuntu Linux';
        } elseif (preg_match('/Fedora/i', $userAgent)) {
            return 'Fedora Linux';
        } elseif (preg_match('/Debian/i', $userAgent)) {
            return 'Debian Linux';
        } elseif (preg_match('/Linux Mint/i', $userAgent)) {
            return 'Linux Mint';
        } elseif (preg_match('/CentOS/i', $userAgent)) {
            return 'CentOS Linux';
        }

        return 'Linux PC';
    }

    /**
     * Get Windows version name from version number.
     *
     * @param  string  $version
     * @return string
     */
    protected function getWindowsVersionName($version)
    {
        $versions = [
            '10.0' => '10/11',
            '6.3' => '8.1',
            '6.2' => '8',
            '6.1' => '7',
            '6.0' => 'Vista',
            '5.1' => 'XP',
        ];

        return $versions[$version] ?? $version;
    }

    /**
     * Get location from IP address.
     * This is a basic implementation. For production, consider using a service like:
     * - ipapi.co
     * - ip-api.com
     * - maxmind.com
     *
     * @param  string  $ipAddress
     * @return array
     */
    protected function getLocationFromIp($ipAddress)
    {
        // Skip local/private IPs
        if ($ipAddress === '127.0.0.1' || $ipAddress === '::1' || !filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return [
                'city' => 'Local',
                'country' => 'Local',
            ];
        }

        // Basic implementation - return null for now
        // In production, you would make an API call to a geolocation service
        // Example:
        // $response = Http::get("http://ip-api.com/json/{$ipAddress}");
        // return [
        //     'city' => $response->json('city'),
        //     'country' => $response->json('country'),
        // ];

        return [
            'city' => null,
            'country' => null,
        ];
    }
}
