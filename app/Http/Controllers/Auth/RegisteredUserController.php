<?php
 
namespace App\Http\Controllers\Auth;
 
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
 
class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }
 
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', \Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
        ]);
 
        // 1. Felhasználó létrehozása
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
 
        // 2. Esemény kiváltása: EZ KÜLDI KI AZ E-MAILT a háttérben
        event(new Registered($user));
 
        // 🟢 3. A DEMÓ TRÜKK: Miután elment az e-mail, azonnal,
        // a háttérben "rákattintunk" a gombra helyetted!
        $user->email_verified_at = now();
        $user->save();
 
        // 4. Bejelentkeztetés
        Auth::login($user);
 
        // 5. Irányítás a főoldalra (már megerősített profilként!)
        return redirect(route('dashboard', absolute: false));
    }
}
