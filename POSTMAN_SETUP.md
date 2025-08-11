# Postman Collection Setup Guide

This guide will help you set up and use the Postman collection for testing the Student Welfare Fund API.

## Files Included

1. `Student_Welfare_Fund_API.postman_collection.json` - The main API collection
2. `Student_Welfare_Fund_API.postman_environment.json` - Environment variables

## Setup Instructions

### 1. Import the Collection

1. Open Postman
2. Click "Import" button
3. Select the `Student_Welfare_Fund_API.postman_collection.json` file
4. The collection will be imported with all endpoints organized by functionality

### 2. Import the Environment

1. In Postman, click "Import" again
2. Select the `Student_Welfare_Fund_API.postman_environment.json` file
3. The environment will be imported with all necessary variables

### 3. Select the Environment

1. In the top-right corner of Postman, select "Student Welfare Fund API - Local" from the environment dropdown
2. This will enable all the variables used in the collection

## Collection Structure

The collection is organized into the following folders:

### 🔐 Authentication
- **Register User** - Create a new user account
- **Login User** - Authenticate and get access token (automatically sets auth_token variable)
- **Get Current User** - Get authenticated user profile
- **Logout User** - Invalidate current token

### 📚 Public Catalog
- **Get Categories** - List all active categories
- **Get Programs** - List programs with filtering and pagination
- **Get Program Details** - Get specific program information
- **Get Recent Donations** - Get recent successful donations

### 💰 Donations
- **Create Quick Donation** - Create a simple donation
- **Create Gift Donation** - Create a gift donation with recipient details
- **Check Donation Status** - Check payment status of a donation
- **Payment Webhook** - Simulate payment provider webhook

### 👤 User Profile
- **Get My Settings** - Get user settings and preferences
- **Update My Settings** - Update user profile and settings
- **Get My Donations** - Get user's donation history

### 📝 Student Applications
- **Create Application** - Submit a new student application
- **Get My Applications** - List user's applications
- **Get Application Details** - Get specific application details

### 🔧 Admin - Categories
- **Get All Categories** - Admin view of all categories
- **Create Category** - Create new category
- **Update Category** - Modify existing category
- **Delete Category** - Remove category

### 🔧 Admin - Programs
- **Get All Programs** - Admin view of all programs
- **Create Program** - Create new program
- **Update Program** - Modify existing program
- **Delete Program** - Remove program

### 🔧 Admin - Applications
- **Get All Applications** - Admin view of all applications
- **Update Application Status** - Approve/reject applications

### 🔧 Admin - Donations
- **Get All Donations** - Admin view of all donations

## Testing Workflow

### 1. Initial Setup
1. Start your Laravel development server: `php artisan serve`
2. Run migrations and seeders: `php artisan migrate --seed`
3. Ensure the API is accessible at `http://localhost:8000`

### 2. Authentication Flow
1. **Register User** - Create a test account
2. **Login User** - This will automatically set the `auth_token` variable
3. Test authenticated endpoints using the token

### 3. Admin Testing
1. Create an admin user in your database or use the seeder
2. Login with admin credentials
3. Manually set the `admin_token` variable in the environment
4. Test admin endpoints

### 4. Testing Donations
1. Create a donation using either quick or gift donation endpoints
2. Note the `donation_id` from the response
3. Use the donation status endpoint to check payment status
4. Test webhook endpoints with the donation ID

## Environment Variables

| Variable | Description | Default Value |
|----------|-------------|---------------|
| `base_url` | API base URL | `http://localhost:8000` |
| `auth_token` | User authentication token | (auto-set on login) |
| `admin_token` | Admin authentication token | (manual set) |
| `user_id` | Current user ID | (auto-set on login) |
| `webhook_signature` | Webhook verification signature | `your_webhook_signature_here` |
| `test_phone` | Test phone number | `+966501234567` |
| `test_password` | Test password | `password123` |
| `test_email` | Test email | `test@example.com` |

## Important Notes

### Authentication
- The "Login User" request includes a test script that automatically sets the `auth_token` variable
- Use this token for all authenticated requests
- The token is a Bearer token, so it's automatically included in the Authorization header

### Idempotency
- Donation creation requests include an `Idempotency-Key` header using `{{$guid}}`
- This prevents duplicate donations if the same request is sent multiple times

### Testing Admin Endpoints
- Admin endpoints require a user with the `admin` role
- You'll need to manually set the `admin_token` variable after logging in as an admin
- Use the same Bearer token format: `Bearer your_admin_token_here`

### Webhook Testing
- The webhook endpoint is for testing payment provider integrations
- Update the `webhook_signature` variable with your actual webhook secret
- The webhook payload includes donation status updates

## Sample Test Data

### User Registration
```json
{
  "phone": "+966501234567",
  "password": "password123",
  "password_confirmation": "password123",
  "email": "user@example.com",
  "name": "John Doe"
}
```

### Quick Donation
```json
{
  "program_id": 1,
  "amount": 100.00,
  "donor_name": "John Doe",
  "note": "For a good cause"
}
```

### Gift Donation
```json
{
  "program_id": 1,
  "amount": 150.00,
  "recipient": {
    "name": "Jane Doe",
    "phone": "+966501234568",
    "message": "Happy Birthday!"
  },
  "sender": {
    "name": "John Doe",
    "phone": "+966501234567",
    "hide_identity": false
  }
}
```

### Student Application
```json
{
  "program_id": 1,
  "personal": {
    "full_name": "Ahmed Mohammed Ali",
    "national_id": "1234567890",
    "date_of_birth": "2000-05-15",
    "gender": "male",
    "address": "Riyadh, Saudi Arabia",
    "phone": "+966501234567",
    "email": "ahmed@example.com"
  },
  "academic": {
    "university": "King Saud University",
    "faculty": "Computer Science",
    "department": "Software Engineering",
    "student_id": "CS123456",
    "gpa": 3.8,
    "academic_year": 3
  },
  "financial": {
    "family_income": 5000.00,
    "family_size": 6,
    "father_occupation": "Teacher",
    "mother_occupation": "Housewife",
    "monthly_expenses": 3000.00,
    "other_sources": "Part-time job"
  }
}
```

## Troubleshooting

### Common Issues

1. **401 Unauthorized**
   - Ensure you're logged in and the `auth_token` is set
   - Check that the token hasn't expired
   - Verify the Authorization header format: `Bearer your_token`

2. **404 Not Found**
   - Check that your Laravel server is running
   - Verify the `base_url` variable is correct
   - Ensure the API routes are properly registered

3. **422 Validation Error**
   - Check the request body format
   - Verify all required fields are included
   - Ensure data types match the expected format

4. **403 Forbidden**
   - For admin endpoints, ensure the user has admin role
   - Check that the `admin_token` is set correctly

### Getting Help

If you encounter issues:
1. Check the Laravel logs: `storage/logs/laravel.log`
2. Verify your database is properly seeded
3. Ensure all migrations have been run
4. Check that the API routes are accessible

## Next Steps

After setting up the Postman collection:
1. Test all endpoints to ensure they work correctly
2. Create additional test cases for edge scenarios
3. Set up automated testing using Postman's Newman CLI
4. Configure webhook testing with your payment provider
5. Set up different environments for staging and production
