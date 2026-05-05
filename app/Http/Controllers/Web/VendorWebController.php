<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use App\Models\VendorUser;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\ImageManager;

class VendorWebController extends Controller
{
    protected string $guard = 'vendor_web';

    public function showRegister()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect()->route('vendor.dashboard');
        }
        return view('web.vendor.register');
    }

    public function register(Request $request)
    {
        $v = Validator::make($request->all(), [
            'shop_name'  => 'required|string|max:255',
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:vendor_users,email',
            'phone'      => 'required|string|regex:/^\+?[0-9]{10,15}$/|unique:vendor_users,phone',
            'shop_address' => 'required|string|max:500',
            'password'   => 'required|string|min:8|confirmed',
            'shop_logo'  => 'nullable|image|mimes:jpeg,png,webp|max:4096',
        ], [
            'email.unique'  => 'This email is already registered.',
            'phone.unique'  => 'This phone number is already registered.',
            'password.confirmed' => 'Passwords do not match.',
        ]);

        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $logoPath = null;
        if ($request->hasFile('shop_logo')) {
            $logoPath = $this->uploadLogo($request->file('shop_logo'));
        }

        DB::table('vendor_users')->insert([
            'first_name'    => strip_tags($request->first_name),
            'last_name'     => strip_tags($request->last_name),
            'email'         => strtolower(trim($request->email)),
            'phone'         => $request->phone,
            'shop_name'     => strip_tags($request->shop_name),
            'shop_address'  => strip_tags($request->shop_address),
            'password'      => Hash::make($request->password),
            'shop_logo'     => $logoPath,
            'status'        => 'pending',
            'product_count' => 0,
            'orders_count'  => 0,
            'auth_token'    => 0,
            'holder_name'   => 0,
            'account_no'    => 0,
            'bank_name'     => 'not set',
            'branch'        => 'not set',
            'minimum_order_amount'          => 0,
            'free_delivery_over_amount'     => 0,
            'free_delivery_status'          => 0,
            'sales_commission_percentage'   => 0,
            'free_delivery_features_status' => '1',
            'free_delivery_responsibility'  => '1',
            'minimum_order_amount_by_seller'=> '0',
        ]);

        return redirect()->route('vendor.register')
            ->with('registered', true);
    }

    public function showLogin()
    {
        if (Auth::guard($this->guard)->check()) {
            return redirect()->route('vendor.dashboard');
        }
        return view('web.vendor.login');
    }

    public function login(Request $request)
    {
        $v = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);
        if ($v->fails()) {
            return back()->withErrors($v)->withInput();
        }

        $key = 'vendor_login:' . sha1($request->input('email') . '|' . $request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $wait = RateLimiter::availableIn($key);
            return back()->withErrors(['email' => "Too many attempts. Try again in {$wait} seconds."])->withInput();
        }

        $vendor = VendorUser::query()->whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (! $vendor) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['email' => 'No account found with this email.'])->withInput();
        }

        if ($vendor->status === 'blocked') {
            return back()->withErrors(['email' => 'Your account has been suspended. Please contact support to resolve this.'])->withInput();
        }

        if ($vendor->status === 'pending') {
            return back()->withErrors(['email' => 'Your application is still under review. We\'ll notify you once approved.'])->withInput();
        }

        if ($vendor->status === 'rejected') {
            return back()->withErrors(['email' => 'Your vendor application was not approved. Please contact support for more information.'])->withInput();
        }

        if (! Hash::check($request->password, $vendor->password)) {
            RateLimiter::hit($key, 60);
            return back()->withErrors(['password' => 'Incorrect password.'])->withInput();
        }

        RateLimiter::clear($key);
        Auth::guard($this->guard)->login($vendor, $request->boolean('remember'));
        $request->session()->save();

        return redirect()->route('vendor.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard($this->guard)->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('vendor.login');
    }

    public function dashboard()
    {
        $vendor = Auth::guard($this->guard)->user();

        if ($vendor->status === 'blocked') {
            Auth::guard($this->guard)->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();
            return redirect()->route('vendor.login')
                ->withErrors(['email' => 'Your account has been suspended. Please contact support to resolve this.']);
        }

        $stats = [
            'products' => DB::table('products_data')->where('vendor_id', $vendor->id)->count(),
            'orders'   => $vendor->orders_count ?? 0,
            'rating'   => $vendor->rating ?? 0,
        ];
        $recentProducts = DB::table('products_data as p')
            ->where('p.vendor_id', $vendor->id)
            ->leftJoinSub(
                DB::table('product_variations')
                    ->select('product_id',
                        DB::raw('MIN(sale_price) as min_sale'),
                        DB::raw('MIN(regular_price) as min_regular'),
                        DB::raw('MAX(regular_price) as max_regular'),
                        DB::raw('BOOL_OR(sale_price > 0 AND sale_price < regular_price) as on_sale')
                    )
                    ->groupBy('product_id'),
                'pv', 'pv.product_id', '=', 'p.id'
            )
            ->orderByDesc('p.created_at')
            ->limit(5)
            ->get(['p.id', 'p.name', 'p.status', 'p.created_at',
                   'pv.min_sale', 'pv.min_regular', 'pv.on_sale']);

        return view('web.vendor.dashboard', compact('vendor', 'stats', 'recentProducts'));
    }

    public function showStoreProfile()
    {
        $vendor = Auth::guard($this->guard)->user();
        return view('web.vendor.store-profile', compact('vendor'));
    }

    public function updateStoreProfile(Request $request)
    {
        $vendor = Auth::guard($this->guard)->user();

        // Handle "remove" button — single image removal
        if ($request->filled('remove_image')) {
            $field = $request->input('remove_image');
            $allowed = ['shop_logo', 'shop_banner', 'secondary_banner', 'bottom_banner', 'offer_banner'];
            if (in_array($field, $allowed)) {
                $oldPath = $vendor->$field;
                if ($oldPath && $oldPath !== 'empty') {
                    Storage::disk('public')->delete($oldPath);
                }
                DB::table('vendor_users')->where('id', $vendor->id)->update([
                    $field => null,
                    'updated_at' => now(),
                ]);
            }
            return back()->with('success', 'Image removed.');
        }

        // Validate all fields
        $request->validate([
            'shop_name'                 => 'required|string|max:255',
            'phone'                     => 'required|string|max:20',
            'shop_address'              => 'required|string|max:500',
            'minimum_order_amount'      => 'nullable|integer|min:0',
            'free_delivery_over_amount' => 'nullable|integer|min:0',
            'free_delivery_status'      => 'nullable|boolean',
            'temporary_close'           => 'nullable|boolean',
            'vacation_status'           => 'nullable|boolean',
            'vacation_start_date'       => 'nullable|date',
            'vacation_end_date'         => 'nullable|date|after_or_equal:vacation_start_date',
            'holder_name'               => 'nullable|string|max:200',
            'bank_name'                 => 'nullable|string|max:200',
            'branch'                    => 'nullable|string|max:200',
            'account_no'                => 'nullable|string|max:50',
            'shop_logo'        => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'shop_banner'      => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'secondary_banner' => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'bottom_banner'    => 'nullable|image|mimes:jpeg,png,webp|max:4096',
            'offer_banner'     => 'nullable|image|mimes:jpeg,png,webp|max:4096',
        ]);

        $vacationOn = $request->boolean('vacation_status');

        $updates = [
            'updated_at'                => now(),
            'shop_name'                 => strip_tags($request->input('shop_name')),
            'phone'                     => $request->input('phone'),
            'shop_address'              => strip_tags($request->input('shop_address')),
            'minimum_order_amount'      => (int) $request->input('minimum_order_amount', 0),
            'free_delivery_over_amount' => (int) $request->input('free_delivery_over_amount', 0),
            'free_delivery_status'      => $request->boolean('free_delivery_status') ? 1 : 0,
            'temporary_close'           => $request->boolean('temporary_close') ? 1 : 0,
            'vacation_status'           => $vacationOn ? 1 : 0,
            'vacation_start_date'       => $vacationOn && $request->filled('vacation_start_date')
                                            ? $request->input('vacation_start_date') : 'empty',
            'vacation_end_date'         => $vacationOn && $request->filled('vacation_end_date')
                                            ? $request->input('vacation_end_date') : 'empty',
            'holder_name'   => strip_tags($request->input('holder_name', '')),
            'bank_name'     => $request->filled('bank_name')  ? strip_tags($request->input('bank_name'))  : 'not set',
            'branch'        => $request->filled('branch')     ? strip_tags($request->input('branch'))     : 'not set',
            'account_no'    => $request->filled('account_no') ? $request->input('account_no') : null,
        ];

        $fields = [
            'shop_logo'        => ['stores/logo',     600,  600],
            'shop_banner'      => ['stores/banner',   1200, 400],
            'secondary_banner' => ['stores/banner',   1200, 400],
            'bottom_banner'    => ['stores/banner',   1200, 400],
            'offer_banner'     => ['stores/banner',   1200, 400],
        ];

        foreach ($fields as $field => [$dir, $w, $h]) {
            if ($request->hasFile($field)) {
                // Delete old file
                $old = $vendor->$field;
                if ($old && $old !== 'empty') {
                    Storage::disk('public')->delete($old);
                }
                $updates[$field] = $this->uploadImage($request->file($field), $dir, $w, $h);
            }
        }

        if (count($updates) > 1) {
            DB::table('vendor_users')->where('id', $vendor->id)->update($updates);
        }

        return back()->with('success', 'Store profile updated successfully.');
    }

    private function uploadLogo($file): string
    {
        return $this->uploadImage($file, 'stores/logo', 600, 600);
    }

    private function uploadImage($file, string $dir, int $maxW, int $maxH): string
    {
        Config::set('image.driver', 'gd');
        $manager = new ImageManager(new Driver);
        $image   = $manager->read($file->getRealPath());
        $image->scaleDown(width: $maxW, height: $maxH);
        $encoded = $image->encode(new JpegEncoder(quality: 85));
        $path    = $dir . '/' . Str::random(40) . '.jpg';
        Storage::disk('public')->makeDirectory($dir);
        Storage::disk('public')->put($path, (string) $encoded);
        return $path;
    }
}
