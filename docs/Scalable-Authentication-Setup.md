# Scalable Authentication Setup Guide

This guide explains how to implement scalable authentication in your Laravel 11 application to handle unlimited concurrent logins efficiently.

## Overview

The scalable authentication system provides:
- Redis-based session management
- Concurrent session limits per user
- Rate limiting for login attempts
- Session monitoring and management
- Load balancing support
- Performance optimization

## Prerequisites

- Docker and Docker Compose
- Laravel 11 application
- Redis server
- Nginx web server

## Configuration Steps

### 1. Environment Variables

Create or update your `.env` file with the following Redis and session configurations:

```env
# Redis Configuration
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
REDIS_CACHE_DB=1
REDIS_SESSION_DB=2

# Session Configuration
SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_EXPIRE_ON_CLOSE=false
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

# Cache Configuration
CACHE_STORE=redis
CACHE_PREFIX=rcs_cache_
CACHE_TTL=3600

# Rate Limiting
LOGIN_THROTTLE_ATTEMPTS=5
LOGIN_THROTTLE_DECAY_MINUTES=1
API_THROTTLE_ATTEMPTS=60
API_THROTTLE_DECAY_MINUTES=1
```

### 2. Docker Services

The following services are configured in `docker-compose.yml`:

- **Redis**: High-performance session and cache storage
- **Nginx**: Load balancer with rate limiting
- **PHP-FPM**: Application server with Redis extension
- **MySQL**: Database server

### 3. Redis Configuration

Redis is configured with:
- Memory limit: 256MB
- Eviction policy: LRU (Least Recently Used)
- Persistence: AOF (Append Only File)
- Separate databases for sessions, cache, and general data

### 4. Session Management

#### Features:
- Maximum 5 concurrent sessions per user
- Session timeout: 120 minutes
- Device information tracking
- Automatic cleanup of expired sessions

#### API Endpoints:

```bash
# Get user sessions
GET /api/sessions

# Terminate specific session
DELETE /api/sessions/{session_id}

# Terminate other sessions
DELETE /api/sessions/others

# Admin: Get session statistics
GET /api/admin/sessions/stats

# Admin: Force logout user
POST /api/admin/sessions/force-logout
```

### 5. Rate Limiting

#### Login Rate Limiting:
- 5 attempts per minute per IP
- Burst allowance: 10 requests
- Automatic lockout after threshold

#### API Rate Limiting:
- 60 requests per minute per IP
- Burst allowance: 100 requests

### 6. Nginx Configuration

#### Performance Optimizations:
- Worker processes: Auto-detected
- Worker connections: 4096 per worker
- Keep-alive connections: 32
- Gzip compression enabled
- Static file caching

#### Security Features:
- Rate limiting zones
- Security headers
- File access restrictions
- Health check endpoint

## Usage Examples

### Creating a Session

```php
use App\Services\ScalableAuthService;

$authService = app(ScalableAuthService::class);
$sessionData = $authService->createSession($user, $sessionId);
```

### Managing Sessions

```php
// Get all user sessions
$sessions = $authService->getUserSessions($user);

// Terminate specific session
$authService->destroySession($user, $sessionId);

// Force logout from all devices
$authService->forceLogoutAll($user);
```

### Rate Limiting in Controllers

```php
use App\Http\Middleware\LoginRateLimit;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('login.rate.limit')->only('login');
    }
}
```

## Monitoring and Maintenance

### Session Statistics

```php
$stats = $authService->getSessionStats();
// Returns: total_users, active_sessions, total_sessions, redis_memory_usage
```

### Redis Monitoring

```bash
# Connect to Redis container
docker exec -it rcs_redis redis-cli

# Monitor Redis operations
MONITOR

# Check memory usage
INFO memory

# List all keys
KEYS *
```

### Performance Monitoring

- Monitor Redis memory usage
- Track session creation/destruction rates
- Monitor rate limiting effectiveness
- Check Nginx access logs for patterns

## Scaling Considerations

### Horizontal Scaling

1. **Multiple PHP-FPM Instances**:
   - Add more app containers
   - Use Redis for session sharing
   - Implement load balancing

2. **Redis Clustering**:
   - Master-slave replication
   - Redis Cluster for high availability
   - Sentinel for automatic failover

3. **Database Optimization**:
   - Connection pooling
   - Read replicas
   - Query optimization

### Performance Tuning

1. **PHP-FPM Settings**:
   ```ini
   pm = dynamic
   pm.max_children = 50
   pm.start_servers = 5
   pm.min_spare_servers = 5
   pm.max_spare_servers = 35
   ```

2. **Redis Optimization**:
   - Memory management
   - Connection pooling
   - Pipeline operations

3. **Nginx Tuning**:
   - Worker processes
   - Connection limits
   - Buffer sizes

## Security Considerations

### Session Security
- Session encryption enabled
- Secure cookies in production
- HTTP-only cookies
- Same-site cookie policy

### Rate Limiting
- IP-based rate limiting
- User-based rate limiting
- Burst allowance configuration
- Automatic lockout mechanisms

### Access Control
- Role-based middleware
- Admin-only endpoints
- Session validation
- CSRF protection

## Troubleshooting

### Common Issues

1. **Redis Connection Failed**:
   - Check Redis container status
   - Verify network connectivity
   - Check Redis configuration

2. **Session Not Persisting**:
   - Verify Redis driver configuration
   - Check session table creation
   - Monitor Redis memory usage

3. **Rate Limiting Too Strict**:
   - Adjust rate limit values
   - Check burst allowances
   - Monitor user patterns

### Debug Commands

```bash
# Check Redis status
docker exec -it rcs_redis redis-cli ping

# View application logs
docker logs rcs_app

# Check Nginx configuration
docker exec -it rcs_webserver nginx -t

# Monitor Redis operations
docker exec -it rcs_redis redis-cli monitor
```

## Best Practices

1. **Session Management**:
   - Regular cleanup of expired sessions
   - Monitor concurrent session limits
   - Implement session timeout warnings

2. **Rate Limiting**:
   - Balance security with usability
   - Monitor false positives
   - Adjust limits based on usage patterns

3. **Performance**:
   - Regular Redis maintenance
   - Monitor memory usage
   - Optimize database queries

4. **Security**:
   - Regular security audits
   - Monitor access patterns
   - Implement logging and alerting

## Conclusion

This scalable authentication system provides a robust foundation for handling unlimited concurrent users while maintaining security and performance. Regular monitoring and maintenance ensure optimal operation as your user base grows.
