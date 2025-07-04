# Laravel Authorization: Manual Checks vs Policies - A Practical Comparison

## Introduction

When building Laravel applications, authorization is a critical aspect that determines what users can and cannot do. Laravel provides multiple approaches to handle authorization, but two of the most common are:

1. **Manual authorization checks** in controllers
2. **Laravel Policies** using the built-in authorization system

In this article, I'll compare these two approaches using a real-world example of a blog post management system. We'll examine the pros and cons of each method and see how they affect code maintainability, readability, and security.

## The Use Case: Blog Post Management

Let's consider a simple blog system where users can:
- View all posts (public)
- View individual posts (public)
- Create new posts (authenticated users only)
- Edit their own posts (post owners only)
- Delete their own posts (post owners only)

## Approach 1: Manual Authorization Checks

Here's how you might implement authorization manually in your controller:

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PostNoPolicyController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user(); 

        if($user){
            $posts = Post::where('user_id', $user->id)->get();
        }else{
            $posts = Post::all();
        }

        return view('posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }

        return view('posts.create');
    }

    public function store(Request $request)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }
        
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]); 

        $post = Post::create($request->all());
        return redirect()->route('posts.index')->with('success', 'Post created successfully');
    }

    public function show(Request $request, Post $post)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }

        //check if the post is owned by the user
        if($post->user_id !== $user->id){
            abort(403);
        }
        
        return view('posts.show', compact('post'));
    }

    public function edit(Request $request, Post $post)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }

        //check if the post is owned by the user
        if($post->user_id !== $user->id){
            abort(403, 'You are not allowed to edit this post');
        }

        return view('posts.edit', compact('post')); 
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }

        //check if the post is owned by the user
        if($post->user_id !== $user->id){
            abort(403, 'You are not allowed to edit this post');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($request->all());
        return redirect()->route('posts.index')->with('success', 'Post updated successfully');
    }

    public function destroy(Request $request, Post $post)
    {
        $user = $request->user(); 

        if(!$user){
            abort(403);
        }

        //check if the post is owned by the user
        if($post->user_id !== $user->id){
            abort(403, 'You are not allowed to delete this post');
        }
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully');
    }
}
```

### Pros of Manual Authorization:
- **Simple to understand**: The logic is right there in the controller
- **Quick to implement**: No additional files or setup required
- **Direct control**: You can customize the authorization logic exactly as needed

### Cons of Manual Authorization:
- **Code duplication**: The same authorization logic is repeated across multiple methods
- **Hard to maintain**: Changes to authorization rules require updating multiple places
- **Testing complexity**: Authorization logic is mixed with business logic
- **Inconsistent error messages**: Different methods might have different error handling
- **Violates DRY principle**: Authorization rules are scattered throughout the codebase

## Approach 2: Laravel Policies

Laravel Policies provide a centralized way to handle authorization logic. Here's how you can implement the same functionality:

### The Policy Class

```php
<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    public function viewAny(?User $user): bool
    {
        return true; // Anyone can view the list of posts
    }

    public function view(?User $user, Post $post): bool
    {
        return true; // Anyone can view individual posts
    }

    public function create(User $user): bool
    {
        return $user !== null; // Only authenticated users can create posts
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // Only post owners can update
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // Only post owners can delete
    }

    public function semak(User $user, Post $post): bool
    {
        return $user->id === $post->user_id; // Only post owners can review
    }
}
```

### The Controller Using Policies

```php
<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index(Request $request)
    {
        if($request->user()->cannot('viewAny', Post::class)){
            abort(403);
        }

        $posts = Post::where('user_id', $request->user()->id)->get();
        return view('posts.index', compact('posts'));
    }

    public function create(Request $request)
    {
        if($request->user()->cannot('create', Post::class)){
            abort(403);
        }

        return view('posts.create');
    }

    public function store(Request $request)
    {
        if($request->user()->cannot('create', Post::class)){
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]); 

        $post = Post::create($request->all());
        return redirect()->route('posts.index')->with('success', 'Post created successfully');
    }

    public function show(Request $request, Post $post)
    {
        if($request->user()->cannot('view', $post)){
            abort(403);
        }

        return view('posts.show', compact('post'));
    }

    public function edit(Request $request, Post $post)
    {
        if($request->user()->cannot('update', $post)){
            abort(403);
        }

        return view('posts.edit', compact('post')); 
    }

    public function update(Request $request, Post $post)
    {
        if($request->user()->cannot('update', $post)){
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($request->all());
        return redirect()->route('posts.index')->with('success', 'Post updated successfully');
    }

    public function destroy(Request $request, Post $post)
    {
        if($request->user()->cannot('delete', $post)){
            abort(403);
        }

        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully');
    }
}
```

### Even Better: Using Policy Authorization in Blade Templates

```php
@can('create', App\Models\Post::class)
    <a href="{{ route('posts.create') }}" class="btn btn-primary">Create New Post</a>
@endcan

@foreach($posts as $post)
    <div class="post">
        <h3>{{ $post->title }}</h3>
        <p>{{ $post->content }}</p>
        
        @can('update', $post)
            <a href="{{ route('posts.edit', $post) }}" class="btn btn-sm btn-warning">Edit</a>
        @endcan
        
        @can('delete', $post)
            <form action="{{ route('posts.destroy', $post) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
            </form>
        @endcan
    </div>
@endforeach
```

## Key Differences and Benefits

### 1. **Centralized Logic**
- **Manual**: Authorization rules are scattered across controller methods
- **Policies**: All authorization logic is centralized in one place

### 2. **Reusability**
- **Manual**: Logic must be duplicated if used in multiple places
- **Policies**: Can be reused in controllers, blade templates, and middleware

### 3. **Testing**
- **Manual**: Authorization logic is mixed with business logic, harder to test
- **Policies**: Can be unit tested independently

### 4. **Maintainability**
- **Manual**: Changes require updating multiple files
- **Policies**: Changes only require updating the policy class

### 5. **Consistency**
- **Manual**: Different error messages and handling across methods
- **Policies**: Consistent authorization behavior throughout the application

## Advanced Policy Features

### 1. **Before Hooks**
```php
public function before(User $user, string $ability): bool|null
{
    if ($user->isAdmin()) {
        return true; // Admins can do everything
    }
    
    return null; // Let other methods handle the authorization
}
```

### 2. **Resource Policies**
```php
// Automatically maps common actions
// index -> viewAny
// show -> view
// create -> create
// store -> create
// edit -> update
// update -> update
// destroy -> delete
```

### 3. **Policy Registration**
```php
// In AuthServiceProvider
protected $policies = [
    Post::class => PostPolicy::class,
];
```

## Best Practices

### 1. **Use Descriptive Method Names**
```php
public function publish(User $user, Post $post): bool
{
    return $user->id === $post->user_id && $post->isDraft();
}
```

### 2. **Handle Edge Cases**
```php
public function update(User $user, Post $post): bool
{
    // Handle null user
    if (!$user) {
        return false;
    }
    
    // Handle published posts
    if ($post->isPublished()) {
        return false;
    }
    
    return $user->id === $post->user_id;
}
```

### 3. **Use Type Declarations**
```php
public function delete(User $user, Post $post): bool
{
    return $user->id === $post->user_id;
}
```

## Performance Considerations

### 1. **Eager Loading**
When using policies with relationships, ensure you eager load the necessary data:

```php
// In your controller
$posts = Post::with('user')->get();

// In your policy
public function update(User $user, Post $post): bool
{
    return $user->id === $post->user->id; // No additional query needed
}
```

### 2. **Caching**
For complex authorization logic, consider caching the results:

```php
public function canAccess(User $user, Post $post): bool
{
    return Cache::remember("user_{$user->id}_post_{$post->id}_access", 300, function () use ($user, $post) {
        // Complex authorization logic here
        return $this->complexAuthorizationCheck($user, $post);
    });
}
```

## Migration Strategy

If you're working with an existing codebase that uses manual authorization, here's a step-by-step migration strategy:

1. **Identify common patterns** in your manual authorization checks
2. **Create policy classes** for each model that needs authorization
3. **Move logic gradually** from controllers to policies
4. **Update blade templates** to use `@can` and `@cannot` directives
5. **Add tests** for your new policies
6. **Remove old manual checks** once everything is working

## Conclusion

While manual authorization checks might seem simpler at first, Laravel Policies provide a much more maintainable and scalable solution. They follow Laravel's convention-over-configuration principle and integrate seamlessly with the framework's authorization system.

**Use Manual Authorization When:**
- Building a simple prototype
- You have very few authorization rules
- You need highly customized logic that doesn't fit the policy pattern

**Use Laravel Policies When:**
- Building a production application
- You have multiple authorization rules
- You want maintainable and testable code
- You plan to reuse authorization logic across different parts of your application

The investment in setting up policies pays off quickly as your application grows, making your code more maintainable, testable, and secure.

---

*What's your experience with Laravel authorization? Do you prefer manual checks or policies? Share your thoughts in the comments below!* 