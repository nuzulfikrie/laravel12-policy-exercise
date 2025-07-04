# PostController and PostPolicy Tests

This directory contains comprehensive tests for the `PostController` and `PostPolicy` classes using Pest PHP testing framework.

## Test Structure

### Feature Tests (`tests/Feature/PostControllerTest.php`)
Tests the complete HTTP request/response cycle for the PostController:

- **Authorization Tests**: Tests all CRUD operations with different user scenarios
- **Data Integrity Tests**: Ensures data is properly saved and retrieved
- **Edge Cases**: Handles edge cases like non-existent posts, malformed data, and XSS attempts

### Unit Tests (`tests/Unit/PostPolicyTest.php`)
Tests the PostPolicy authorization logic in isolation:

- **Policy Method Tests**: Tests each policy method individually
- **Edge Cases**: Tests null users, different data types, etc.
- **Integration Tests**: Tests policy integration with Laravel's Gate facade
- **Performance Tests**: Ensures authorization checks are efficient

### Test Helpers (`tests/Helpers/PostTestHelper.php`)
Provides reusable helper methods for common test scenarios.

## Running the Tests

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Files
```bash
# Run only feature tests
php artisan test tests/Feature/PostControllerTest.php

# Run only unit tests
php artisan test tests/Unit/PostPolicyTest.php
```

### Run Tests with Coverage
```bash
php artisan test --coverage
```

### Run Tests in Parallel
```bash
php artisan test --parallel
```

## Test Scenarios Covered

### PostController Feature Tests

#### Index Method
- ✅ Authenticated user can view their own posts
- ✅ Unauthenticated users are denied access

#### Create Method
- ✅ Authenticated user can access create form
- ✅ Unauthenticated users are denied access

#### Store Method
- ✅ Authenticated user can create posts
- ✅ Unauthenticated users are denied access
- ✅ Validation errors are properly handled
- ✅ Title length validation works

#### Show Method
- ✅ Post owner can view their post
- ✅ Other users are denied access
- ✅ Unauthenticated users are denied access

#### Edit Method
- ✅ Post owner can access edit form
- ✅ Other users are denied access
- ✅ Unauthenticated users are denied access

#### Update Method
- ✅ Post owner can update their post
- ✅ Other users are denied access
- ✅ Unauthenticated users are denied access
- ✅ Validation errors are properly handled

#### Destroy Method
- ✅ Post owner can delete their post
- ✅ Other users are denied access
- ✅ Unauthenticated users are denied access

### PostPolicy Unit Tests

#### viewAny Method
- ✅ Allows anyone to view posts list
- ✅ Allows unauthenticated users

#### view Method
- ✅ Allows anyone to view individual posts
- ✅ Allows unauthenticated users
- ✅ Allows other users to view posts they don't own

#### create Method
- ✅ Allows authenticated users to create posts
- ✅ Denies unauthenticated users

#### update Method
- ✅ Allows post owner to update their post
- ✅ Denies other users from updating posts they don't own
- ✅ Denies unauthenticated users
- ✅ Correctly compares user IDs

#### delete Method
- ✅ Allows post owner to delete their post
- ✅ Denies other users from deleting posts they don't own
- ✅ Denies unauthenticated users
- ✅ Correctly compares user IDs

#### semak Method
- ✅ Allows post owner to review their post
- ✅ Denies other users from reviewing posts they don't own
- ✅ Denies unauthenticated users

## Test Data

The tests use Laravel factories to create test data:

- `User::factory()->create()` - Creates test users
- `Post::factory()->create()` - Creates test posts
- `Post::factory()->create(['user_id' => $user->id])` - Creates posts for specific users

## Database

Tests use `RefreshDatabase` trait to ensure a clean database state for each test.

## Authorization Testing

The tests verify that Laravel's authorization system works correctly:

1. **Policy Registration**: Ensures policies are properly registered
2. **Gate Integration**: Tests integration with Laravel's Gate facade
3. **User Scenarios**: Tests authenticated, unauthenticated, and different user scenarios
4. **Edge Cases**: Tests null users, different data types, etc.

## Performance Considerations

The tests include performance benchmarks to ensure authorization checks are efficient:

- 1000 authorization checks should complete in < 1 second
- 100 post checks should complete in < 0.1 seconds

## Best Practices Demonstrated

1. **Test Isolation**: Each test is independent and doesn't rely on other tests
2. **Descriptive Names**: Test names clearly describe what is being tested
3. **Edge Case Coverage**: Tests handle null values, invalid data, etc.
4. **Performance Testing**: Includes performance benchmarks
5. **Helper Methods**: Reusable test helpers reduce code duplication
6. **Database Assertions**: Verifies data integrity in the database
7. **HTTP Assertions**: Tests proper HTTP status codes and responses

## Troubleshooting

### Common Issues

1. **Database Connection**: Ensure your test database is properly configured
2. **Factory Issues**: Make sure all required factories are defined
3. **Route Issues**: Ensure all routes are properly defined in `routes/web.php`
4. **Policy Registration**: Ensure policies are registered in `AuthServiceProvider`

### Debugging Tests

```bash
# Run tests with verbose output
php artisan test --verbose

# Run a specific test
php artisan test --filter "test_name"

# Run tests and stop on first failure
php artisan test --stop-on-failure
``` 