# Laravel Policy Capabilities Showcase


[![Test Coverage](https://raw.githubusercontent.com/nuzulfikrie/laravel12-policy-exercise/main/badge-coverage.svg)](https://github.com/nuzulfikrie/laravel12-policy-exercise)
A comprehensive demonstration of Laravel's authorization system, comparing manual authorization checks with Laravel Policies. This project showcases best practices for implementing secure, maintainable authorization in Laravel applications.

## 🎯 Project Overview

This project demonstrates two approaches to authorization in Laravel:

1. **Manual Authorization** (`PostNoPolicyController`) - Direct checks in controllers
2. **Laravel Policies** (`PostController` + `PostPolicy`) - Centralized authorization logic

### Key Features

- ✅ **Policy-Driven Authorization** - Centralized, reusable authorization logic
- ✅ **Manual Authorization Comparison** - See the difference between approaches
- ✅ **Comprehensive Testing** - Unit and feature tests for both approaches
- ✅ **Static Analysis** - PHPStan integration for code quality
- ✅ **Modern UI Dashboard** - Beautiful policy capabilities showcase
- ✅ **Custom Policy Methods** - Including custom `semak` (review) action

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 22+ (for frontend assets)
- Database (MySQL, PostgreSQL, or SQLite)

### Installation

1. **Clone the repository**
   ```bash
   git clone <your-repo-url>
   cd example-policy
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

6. **Visit the application**
   - Main dashboard: `http://localhost:8000/posts`
   - Login: `admin@admin.com` / `changeme`

## 📊 Policy vs Manual Authorization

### Manual Authorization (PostNoPolicyController)

```php
public function show(Request $request, Post $post): View
{
    $user = $request->user();

    if (!$user) {
        abort(403);
    }

    // Manual check - repeated in every method
    if ($post->user_id !== $user->id) {
        abort(403);
    }

    return view('posts.show', compact('post'));
}
```

**Pros:**
- Simple to understand
- Quick to implement
- Direct control

**Cons:**
- Code duplication
- Hard to maintain
- Inconsistent error handling
- Violates DRY principle

### Laravel Policies (PostController + PostPolicy)

```php
// PostPolicy.php
public function view(?User $user, Post $post): bool
{
    return $user && $user->id == $post->user_id;
}

// PostController.php
public function show(Request $request, Post $post): View
{
    if ($request->user()->cannot('view', $post)) {
        abort(403);
    }

    return view('posts.show', compact('post'));
}

// Blade template
@can('view', $post)
    <a href="{{ route('posts.show', $post) }}">View</a>
@endcan
```

**Pros:**
- Centralized logic
- Reusable across controllers, views, and middleware
- Testable independently
- Consistent behavior
- Follows Laravel conventions

## 🏗️ Project Structure

```
app/
├── Http/Controllers/
│   ├── PostController.php          # Uses Laravel Policies
│   ├── PostNoPolicyController.php  # Manual authorization
│   └── Auth/LoginController.php    # Authentication
├── Models/
│   ├── Post.php                    # Eloquent model with relationships
│   └── User.php                    # User model
├── Policies/
│   └── PostPolicy.php              # Authorization policy
└── ...

tests/
├── Feature/
│   ├── PostControllerTest.php      # Feature tests for policy approach
│   └── PostNoPolicyControllerTest.php
├── Unit/
│   └── PostPolicyTest.php          # Unit tests for policy
└── Helpers/
    └── PostTestHelper.php          # Test utilities

database/
├── migrations/                     # Database schema
├── factories/                      # Model factories
└── seeders/                       # Database seeders
```

## 🔐 Authorization Methods

### Standard CRUD Operations

| Action | Policy Method | Description |
|--------|---------------|-------------|
| `viewAny` | `viewAny(?User $user)` | View list of posts |
| `view` | `view(?User $user, Post $post)` | View individual post |
| `create` | `create(?User $user)` | Create new post |
| `update` | `update(?User $user, Post $post)` | Edit existing post |
| `delete` | `delete(?User $user, Post $post)` | Delete post |

### Custom Actions

| Action | Policy Method | Description |
|--------|---------------|-------------|
| `semak` | `semak(?User $user, Post $post)` | Review post (custom action) |

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suites
```bash
# Policy tests
php artisan test --filter=PostPolicyTest

# Feature tests
php artisan test --filter=PostControllerTest


### Static Analysis
```bash
# Run PHPStan
./vendor/bin/phpstan analyse

# Run with specific level
./vendor/bin/phpstan analyse
```

## 🎨 Dashboard Features

The policy dashboard (`/posts`) showcases:

- **Statistics Overview** - Total posts, reviewed posts, user-specific counts
- **Policy Demo Section** - Explains policy capabilities
- **Authorization-Protected Actions** - All buttons/links use `@can` directives
- **Real-time Policy Checks** - Actions appear/disappear based on user permissions
- **Modern UI** - Clean, responsive design with policy badges

## 🔧 Configuration

### PHPStan Configuration

The project includes a comprehensive PHPStan setup:

```yaml
# phpstan.neon
includes:
    - ./vendor/larastan/larastan/extension.neon
    - phpstan-baseline.neon

parameters:
    level: 6
    paths:
        - app/
        - config/
        - database/
        - routes/
        - tests/
```

### Policy Registration

Policies are automatically discovered by Laravel, but you can explicitly register them in `AuthServiceProvider`:

```php
protected $policies = [
    Post::class => PostPolicy::class,
];
```

## 📈 Performance Considerations

### Eager Loading
```php
// In controller
$posts = Post::with('user')->get();

// In policy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user->id; // No N+1 query
}
```

### Caching
```php
public function canAccess(User $user, Post $post): bool
{
    return Cache::remember("user_{$user->id}_post_{$post->id}_access", 300, function () use ($user, $post) {
        return $this->complexAuthorizationCheck($user, $post);
    });
}
```

## 🚨 Security Best Practices

1. **Always validate user input**
2. **Use policies for authorization, not just authentication**
3. **Test authorization logic thoroughly**
4. **Use type declarations for better security**
5. **Implement proper error handling**
6. **Log authorization failures for monitoring**

## 🔄 Migration Strategy

If you're migrating from manual authorization:

1. **Identify patterns** in existing authorization checks
2. **Create policy classes** for each model
3. **Move logic gradually** from controllers to policies
4. **Update blade templates** to use `@can` directives
5. **Add comprehensive tests**
6. **Remove old manual checks**

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🙏 Acknowledgments

- [Laravel Framework](https://laravel.com) - The amazing PHP framework
- [Larastan](https://github.com/larastan/larastan) - PHPStan extension for Laravel
- [Pest](https://pestphp.com) - Elegant testing framework

---

**Built with ❤️ using Laravel 12 and modern PHP practices**

For questions or support, please open an issue on GitHub.
