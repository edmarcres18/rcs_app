# Telegram Bot Setup Guide

This document explains how to set up and use the Telegram bot integration with the MHR Reporting Compliance System.

## Configuration

### Environment Variables

Add these variables to your `.env` file:

```
# Telegram Bot API Token
TELEGRAM_BOT_API=8008064411:AAHr-_FkV3Q8nNF2sInkhncD6ZZBJbKRyyc

# Production webhook URL (must be HTTPS)
TELEGRAM_WEBHOOK_URL=https://your-production-domain.com/api/telegram/webhook

# Comma-separated list of admin chat IDs
TELEGRAM_ADMIN_CHAT_IDS=

# Enable debugging (optional)
TELEGRAM_DEBUG=false

# Local development settings
TELEGRAM_LOCAL_USE_POLLING=true
TELEGRAM_USE_NGROK=false
TELEGRAM_NGROK_URL=
```

## Database Migration

Run the migration to add Telegram chat IDs to users:

```bash
php artisan migrate
```

## Setup Instructions

### Production Environment

1. Make sure your server has HTTPS enabled
2. Set the `TELEGRAM_WEBHOOK_URL` in your `.env` file to your production URL
3. Run the webhook setup command:

```bash
php artisan telegram:setup-webhook
```

4. Verify the webhook is working by running:

```bash
php artisan telegram:test-message YOUR_CHAT_ID
```

### Local Development Environment

You have two options for local development:

#### Option 1: Long Polling (Recommended for local testing)

1. Make sure `TELEGRAM_LOCAL_USE_POLLING=true` in your `.env` file
2. Run the polling command:

```bash
php artisan telegram:poll
```

This will start a long-running process that checks for new messages from Telegram.

#### Option 2: Use ngrok for local webhook testing

1. Install ngrok: [https://ngrok.com/download](https://ngrok.com/download)
2. Start ngrok on your Laravel server port:

```bash
ngrok http 8000
```

3. Update your `.env` file:

```
TELEGRAM_LOCAL_USE_POLLING=false
TELEGRAM_USE_NGROK=true
```

4. Run the webhook setup command:

```bash
php artisan telegram:setup-webhook --ngrok
```

## Testing the Bot

1. Open Telegram and search for your bot (by username)
2. Send the `/start` command to the bot
3. You should receive the welcome message: "Good Success! Welcome to MHR Reporting Compliance System Notifications"

## Useful Commands

### Test Sending Messages

```bash
php artisan telegram:test-message YOUR_CHAT_ID "Your custom message"
```

### Check Webhook Status

```bash
php artisan telegram:webhook-info
```

### Delete Webhook (to switch to polling)

```bash
php artisan telegram:delete-webhook
```

## Troubleshooting

1. **Bot not responding**: Check if the webhook is properly set up with `php artisan telegram:webhook-info`
2. **Local testing issues**: Make sure ngrok is running or switch to polling mode
3. **Permission denied errors**: Ensure your web server has appropriate permissions
4. **SSL issues**: Telegram requires HTTPS for webhooks, ensure your SSL certificate is valid

## Security Considerations

- Keep your bot token secure and never commit it to public repositories
- Use environment variables for sensitive information
- Implement rate limiting for bot commands if needed
- Be cautious about what information is sent via Telegram 
