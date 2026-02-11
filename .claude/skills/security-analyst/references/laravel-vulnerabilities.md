# Laravel Vulnerability Patterns

Reference for common vulnerability patterns in Laravel applications.

---

## A03 - Injection Vulnerabilities

### SQL Injection

**Pattern 1: Raw Query Concatenation**
```php
// ❌ VULNERABLE
$users = DB::select("SELECT * FROM users WHERE id = " . $request->id);
$posts = DB::raw("SELECT * FROM posts WHERE title = '" . $title . "'");

// ✅ SECURE
$users = DB::select("SELECT * FROM users WHERE id = ?", [$request->id]);
$posts = DB::select("SELECT * FROM posts WHERE title = ?", [$title]);
```

**Pattern 2: whereRaw with User Input**
```php
// ❌ VULNERABLE
User::whereRaw("email = '$email'")->first();

// ✅ SECURE
User::whereRaw("email = ?", [$email])->first();
User::where('email', $email)->first(); // Preferred
```

**Pattern 3: orderByRaw with User Input**
```php
// ❌ VULNERABLE
$sortColumn = $request->input('sort');
User::orderByRaw($sortColumn)->get();

// ✅ SECURE
$allowed = ['name', 'email', 'created_at'];
$sortColumn = in_array($request->input('sort'), $allowed) 
    ? $request->input('sort') 
    : 'created_at';
User::orderBy($sortColumn)->get();
```

### XSS (Cross-Site Scripting)

**Pattern 1: Unescaped Blade Output**
```php
// ❌ VULNERABLE
{!! $user->bio !!}
{!! request('search') !!}

// ✅ SECURE
{{ $user->bio }}
{{ request('search') }}

// If HTML is needed, sanitize first
{!! clean($user->bio) !!} // Using HTML Purifier
```

**Pattern 2: JavaScript Context**
```php
// ❌ VULNERABLE
<script>
    var data = "{{ $userInput }}";
</script>

// ✅ SECURE
<script>
    var data = @json($userInput);
</script>
```

**Pattern 3: Attribute Context**
```php
// ❌ VULNERABLE
<input value="{{ $userInput }}">

// ✅ SECURE (Blade auto-escapes, but be explicit)
<input value="{{ e($userInput) }}">
```

### Command Injection

**Pattern 1: exec/shell_exec with User Input**
```php
// ❌ VULNERABLE
exec("convert " . $request->file . " output.png");
shell_exec("grep " . $search . " /var/log/app.log");

// ✅ SECURE
exec("convert " . escapeshellarg($request->file) . " output.png");
// Or better: use Process component
use Symfony\Component\Process\Process;
$process = new Process(['convert', $filename, 'output.png']);
```

### LDAP Injection

**Pattern 1: Unescaped LDAP Filter**
```php
// ❌ VULNERABLE
$filter = "(uid=" . $username . ")";

// ✅ SECURE
$filter = "(uid=" . ldap_escape($username, '', LDAP_ESCAPE_FILTER) . ")";
```

---

## A01 - Broken Access Control

### Missing Authorization

**Pattern 1: No Policy Check**
```php
// ❌ VULNERABLE
public function update(Request $request, Post $post)
{
    $post->update($request->all());
}

// ✅ SECURE
public function update(Request $request, Post $post)
{
    $this->authorize('update', $post);
    $post->update($request->validated());
}
```

**Pattern 2: IDOR (Insecure Direct Object Reference)**
```php
// ❌ VULNERABLE - User can access any order
public function show($orderId)
{
    return Order::findOrFail($orderId);
}

// ✅ SECURE - Scoped to user
public function show($orderId)
{
    return auth()->user()->orders()->findOrFail($orderId);
}
```

**Pattern 3: Missing Middleware**
```php
// ❌ VULNERABLE - No auth check
Route::get('/admin/users', [AdminController::class, 'users']);

// ✅ SECURE
Route::middleware(['auth', 'can:admin'])
    ->get('/admin/users', [AdminController::class, 'users']);
```

### Privilege Escalation

**Pattern 1: Role Assignment**
```php
// ❌ VULNERABLE - User can set their own role
$user->update($request->all());

// ✅ SECURE - Exclude sensitive fields
$user->update($request->only(['name', 'email']));
// Or use $fillable properly
```

---

## A02 - Cryptographic Failures

### Weak Encryption

**Pattern 1: Insecure Hashing**
```php
// ❌ VULNERABLE
$hash = md5($password);
$hash = sha1($password);

// ✅ SECURE
$hash = Hash::make($password);
$hash = bcrypt($password);
```

**Pattern 2: Hardcoded Secrets**
```php
// ❌ VULNERABLE
$apiKey = "sk-1234567890abcdef";
$secret = "my-secret-key";

// ✅ SECURE
$apiKey = config('services.api.key');
$secret = env('APP_SECRET');
```

**Pattern 3: Weak Random**
```php
// ❌ VULNERABLE
$token = rand(1000, 9999);
$token = mt_rand();

// ✅ SECURE
$token = Str::random(32);
$token = bin2hex(random_bytes(16));
```

---

## A05 - Security Misconfiguration

### Debug Mode

**Pattern 1: Debug in Production**
```php
// ❌ VULNERABLE - in .env.production
APP_DEBUG=true

// ✅ SECURE
APP_DEBUG=false
```

### Verbose Errors

**Pattern 1: Exception Details Exposed**
```php
// ❌ VULNERABLE
catch (Exception $e) {
    return response()->json(['error' => $e->getMessage()]);
}

// ✅ SECURE
catch (Exception $e) {
    Log::error($e->getMessage());
    return response()->json(['error' => 'An error occurred']);
}
```

### Default Credentials

**Pattern 1: Default Admin Password**
```php
// ❌ VULNERABLE
User::create([
    'email' => 'admin@example.com',
    'password' => bcrypt('admin123'),
]);

// ✅ SECURE - Force password change or use env
User::create([
    'email' => 'admin@example.com',
    'password' => bcrypt(env('ADMIN_INITIAL_PASSWORD')),
    'must_change_password' => true,
]);
```

---

## A07 - Identification and Authentication Failures

### Session Security

**Pattern 1: Session Fixation**
```php
// ❌ VULNERABLE - Session not regenerated
Auth::login($user);

// ✅ SECURE
Auth::login($user);
session()->regenerate();
```

**Pattern 2: Insecure Session Config**
```php
// ❌ VULNERABLE - in config/session.php
'secure' => false,
'http_only' => false,

// ✅ SECURE
'secure' => env('SESSION_SECURE_COOKIE', true),
'http_only' => true,
'same_site' => 'lax',
```

### Brute Force

**Pattern 1: No Rate Limiting**
```php
// ❌ VULNERABLE
Route::post('/login', [AuthController::class, 'login']);

// ✅ SECURE
Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

---

## A08 - Software and Data Integrity Failures

### Insecure Deserialization

**Pattern 1: unserialize with User Input**
```php
// ❌ VULNERABLE
$data = unserialize($request->input('data'));

// ✅ SECURE
$data = json_decode($request->input('data'), true);
// Or with allowed classes
$data = unserialize($input, ['allowed_classes' => false]);
```

### Mass Assignment

**Pattern 1: No Fillable/Guarded**
```php
// ❌ VULNERABLE
class User extends Model
{
    // No $fillable or $guarded
}
User::create($request->all());

// ✅ SECURE
class User extends Model
{
    protected $fillable = ['name', 'email'];
    // OR
    protected $guarded = ['id', 'is_admin', 'role'];
}
```

---

## A10 - Server-Side Request Forgery (SSRF)

### Unvalidated URLs

**Pattern 1: Fetching User-Provided URLs**
```php
// ❌ VULNERABLE
$response = Http::get($request->input('url'));

// ✅ SECURE - Validate URL
$url = $request->input('url');
$parsed = parse_url($url);

// Block internal IPs
$blockedHosts = ['localhost', '127.0.0.1', '0.0.0.0', '169.254.169.254'];
if (in_array($parsed['host'], $blockedHosts)) {
    abort(403, 'Invalid URL');
}

// Whitelist allowed domains
$allowedDomains = ['api.example.com', 'cdn.example.com'];
if (!in_array($parsed['host'], $allowedDomains)) {
    abort(403, 'Domain not allowed');
}

$response = Http::get($url);
```

---

## File Upload Vulnerabilities

### Unrestricted Upload

**Pattern 1: No Validation**
```php
// ❌ VULNERABLE
$path = $request->file('document')->store('uploads');

// ✅ SECURE
$request->validate([
    'document' => [
        'required',
        'file',
        'mimes:pdf,doc,docx',
        'max:10240', // 10MB
    ],
]);
$path = $request->file('document')->store('uploads');
```

**Pattern 2: Executable Upload**
```php
// ❌ VULNERABLE - Allows PHP files
'mimes:jpg,png,gif,php'

// ✅ SECURE
'mimes:jpg,png,gif'
// Also validate actual content
$request->validate([
    'image' => 'required|image|mimes:jpg,png,gif|max:2048',
]);
```

**Pattern 3: Path Traversal**
```php
// ❌ VULNERABLE
$filename = $request->input('filename');
Storage::put($filename, $content);

// ✅ SECURE
$filename = basename($request->input('filename'));
$filename = Str::slug(pathinfo($filename, PATHINFO_FILENAME)) 
    . '.' . pathinfo($filename, PATHINFO_EXTENSION);
Storage::put($filename, $content);
```

---

## CSRF Vulnerabilities

### Missing CSRF Token

**Pattern 1: Form Without Token**
```html
<!-- ❌ VULNERABLE -->
<form method="POST" action="/update">
    <input type="text" name="name">
    <button type="submit">Update</button>
</form>

<!-- ✅ SECURE -->
<form method="POST" action="/update">
    @csrf
    <input type="text" name="name">
    <button type="submit">Update</button>
</form>
```

**Pattern 2: API Without Protection**
```php
// ❌ VULNERABLE - State-changing GET
Route::get('/delete/{id}', [PostController::class, 'delete']);

// ✅ SECURE - Use appropriate method
Route::delete('/posts/{id}', [PostController::class, 'delete']);
```

---

## Logging Sensitive Data

### Sensitive Data in Logs

**Pattern 1: Logging Passwords**
```php
// ❌ VULNERABLE
Log::info('User registered', $request->all());

// ✅ SECURE
Log::info('User registered', [
    'email' => $request->email,
    'name' => $request->name,
    // Exclude password
]);
```

**Pattern 2: Credit Card in Logs**
```php
// ❌ VULNERABLE
Log::info('Payment processed', ['card' => $cardNumber]);

// ✅ SECURE
Log::info('Payment processed', [
    'card_last4' => substr($cardNumber, -4),
]);
```

---

## Quick Detection Patterns

Use these regex patterns to quickly identify potential vulnerabilities:

```
# SQL Injection
DB::raw\s*\([^?]
whereRaw\s*\([^?]
"SELECT.*\$
'SELECT.*\$

# XSS
\{!!\s*\$
\{!!\s*request\(

# Command Injection
exec\s*\(.*\$
shell_exec\s*\(.*\$
system\s*\(.*\$
passthru\s*\(.*\$

# Weak Crypto
md5\s*\(
sha1\s*\(
rand\s*\(
mt_rand\s*\(

# Deserialization
unserialize\s*\(.*\$

# File Operations
file_get_contents\s*\(.*\$request
file_put_contents\s*\(.*\$request
```
