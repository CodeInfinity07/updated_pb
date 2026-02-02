# Laravel Reverb WebSocket Server - Systemd Service Configuration

This document provides the systemd service configuration for running Laravel Reverb WebSocket server on your VPS.

## Prerequisites

- PHP 8.2+ installed
- Laravel application deployed
- Reverb configuration complete

## Service File

Create the service file at `/etc/systemd/system/laravel-reverb.service`:

```ini
[Unit]
Description=Laravel Reverb WebSocket Server
After=network.target

[Service]
User=www-data
Group=www-data
WorkingDirectory=/var/www/your-app
ExecStart=/usr/bin/php artisan reverb:start --host=0.0.0.0 --port=8080
Restart=always
RestartSec=5
StandardOutput=append:/var/log/laravel-reverb.log
StandardError=append:/var/log/laravel-reverb-error.log

[Install]
WantedBy=multi-user.target
```

## Environment Variables

Add these to your `.env` file:

```env
BROADCAST_CONNECTION=reverb

REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

For production with SSL (recommended):

```env
REVERB_SCHEME=https
REVERB_PORT=443
```

## Installation Commands

```bash
# Copy service file
sudo cp laravel-reverb.service /etc/systemd/system/

# Reload systemd
sudo systemctl daemon-reload

# Enable service (starts on boot)
sudo systemctl enable laravel-reverb

# Start service
sudo systemctl start laravel-reverb

# Check status
sudo systemctl status laravel-reverb

# View logs
sudo journalctl -u laravel-reverb -f
```

## Nginx WebSocket Proxy Configuration

Add this to your Nginx site configuration:

```nginx
# WebSocket location block
location /app {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
    proxy_read_timeout 86400;
}

# Broadcasting auth endpoint
location /broadcasting/auth {
    proxy_pass http://127.0.0.1:8000;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
}
```

## Useful Commands

```bash
# Restart service
sudo systemctl restart laravel-reverb

# Stop service
sudo systemctl stop laravel-reverb

# Disable service (won't start on boot)
sudo systemctl disable laravel-reverb

# Check if running
sudo systemctl is-active laravel-reverb

# View real-time logs
tail -f /var/log/laravel-reverb.log
```

## Generating Reverb Credentials

You can generate random credentials using:

```bash
php artisan reverb:env
```

Or manually generate:
- APP_ID: Any unique string (e.g., `br-bot-app`)
- APP_KEY: 32-character random string
- APP_SECRET: 32-character random string

## Troubleshooting

1. **Port already in use**: Change the port in the service file and .env
2. **Permission denied**: Ensure the user has access to the Laravel directory
3. **Connection refused**: Check if the service is running and the port is open in firewall
4. **SSL issues**: Use Nginx to proxy WebSocket connections with SSL termination

## Testing Connection

You can test the WebSocket connection using:

```bash
# Check if Reverb is listening
netstat -tlnp | grep 8080

# Test WebSocket handshake
curl -i -N -H "Connection: Upgrade" -H "Upgrade: websocket" -H "Sec-WebSocket-Version: 13" -H "Sec-WebSocket-Key: test" http://localhost:8080/app/your-app-key
```
