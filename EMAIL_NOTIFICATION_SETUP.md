# Email Notification Setup Guide

## Environment Variables Required

Add the following environment variables to your Laravel `.env` file:

```env
# Supabase Configuration (existing)
SUPABASE_URL=your_supabase_project_url
SUPABASE_ANON_KEY=your_supabase_anon_key
SUPABASE_SERVICE_ROLE_KEY=your_supabase_service_role_key

# Portal Configuration
PORTAL_URL=https://your-portal-domain.com
APP_NAME="College Placement Portal"

# SendGrid Configuration (for Edge Function)
# Note: These are set directly in Supabase, not in Laravel
# SENDGRID_API_KEY=your_sendgrid_api_key
# FROM_EMAIL=noreply@yourcollegedomain.com
# FROM_NAME="College Placement Portal"
```

## Supabase Edge Function Environment Setup

### 1. Install Supabase CLI
```bash
npm install -g supabase
```

### 2. Login to Supabase
```bash
supabase login
```

### 3. Link Your Project
```bash
supabase link --project-ref your-project-reference-id
```

### 4. Deploy the Edge Function
```bash
supabase functions deploy send-status-email
```

### 5. Set Environment Variables in Supabase
```bash
# Set your SendGrid API key
supabase secrets set SENDGRID_API_KEY=your_sendgrid_api_key_here

# Set your from email address
supabase secrets set FROM_EMAIL=noreply@yourcollegedomain.com

# Set your from name
supabase secrets set FROM_NAME="College Placement Portal"

# Set your portal URL for email links
supabase secrets set PORTAL_URL=https://your-portal-domain.com
```

## SendGrid Setup Instructions

### 1. Create SendGrid Account
- Go to https://sendgrid.com/
- Sign up for a free account (100 emails/day free tier)

### 2. Verify Your Sender Identity
- Go to Settings > Sender Authentication
- Verify a single sender email OR set up domain authentication
- Use this verified email as your `FROM_EMAIL`

### 3. Create API Key
- Go to Settings > API Keys
- Click "Create API Key"
- Choose "Restricted Access"
- Give it "Mail Send" permissions only
- Copy the generated API key for `SENDGRID_API_KEY`

### 4. Test Your Setup
```bash
# Test the edge function directly
curl -X POST "https://your-project-ref.supabase.co/functions/v1/send-status-email" \
  -H "Authorization: Bearer your-anon-key" \
  -H "Content-Type: application/json" \
  -d '{
    "student_email": "test@example.com",
    "student_name": "Test Student",
    "status": "approved"
  }'
```

## Alternative Email Providers

### Using Resend (Alternative to SendGrid)
If you prefer Resend over SendGrid, modify the Edge Function:

1. Replace SendGrid API calls with Resend API
2. Set `RESEND_API_KEY` instead of `SENDGRID_API_KEY`
3. Update the fetch URL to `https://api.resend.com/emails`

### Using Gmail SMTP (Not Recommended for Production)
For development only, you can modify the Edge Function to use SMTP:

```typescript
// Add to Edge Function
import { SMTPClient } from "https://deno.land/x/denomailer@1.6.0/mod.ts";
```

## Security Best Practices

1. **Never expose API keys in frontend code**
2. **Use environment variables for all sensitive data**
3. **Regularly rotate your SendGrid API keys**
4. **Set up proper CORS policies in Supabase**
5. **Monitor email sending quotas and usage**

## Troubleshooting

### Common Issues:

1. **Edge Function Not Found**
   - Ensure function is deployed: `supabase functions list`
   - Check function name matches exactly: `send-status-email`

2. **SendGrid Authentication Failed**
   - Verify API key is correct and has Mail Send permissions
   - Check sender email is verified in SendGrid

3. **CORS Errors**
   - Edge function includes proper CORS headers
   - Check browser console for specific CORS issues

4. **Email Not Received**
   - Check spam/junk folders
   - Verify recipient email address
   - Check SendGrid Activity feed for delivery status

### Logs and Monitoring

View Edge Function logs:
```bash
supabase functions logs send-status-email
```

View Laravel logs for email notifications:
```bash
tail -f storage/logs/laravel.log | grep "status email"
```

## Production Considerations

1. **Scale SendGrid plan** based on expected email volume
2. **Set up email templates** in SendGrid for better management
3. **Implement retry logic** for failed email sends
4. **Monitor bounce rates** and maintain sender reputation
5. **Consider adding unsubscribe links** for compliance
6. **Set up webhooks** for delivery status tracking