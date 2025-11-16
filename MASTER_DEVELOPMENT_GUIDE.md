# Master Development Guide

## 🎯 Project Overview

**SIKOPMA** (Sistem Informasi Koperasi) adalah aplikasi manajemen koperasi berbasis Laravel 12 dengan Livewire v3, TailwindCSS v4, dan Alpine.js.

### Tech Stack
- **Backend**: Laravel 12, PHP 8.2+
- **Frontend**: Livewire v3, TailwindCSS v4, Alpine.js
- **Database**: MySQL 8.0+
- **Authentication**: Laravel Breeze + Spatie Permission
- **Testing**: PHPUnit, PEST
- **Code Quality**: Laravel Pint
- **CI/CD**: GitHub Actions

## 🏗️ Architecture Standards

### Directory Structure
```
app/
├── Http/Controllers/          # HTTP Controllers
├── Livewire/                  # Livewire Components
│   ├── Auth/                  # Authentication components
│   ├── Dashboard/             # Dashboard components
│   ├── Master/                # Master data components
│   ├── Schedule/              # Schedule management
│   ├── Swap/                  # Shift swap system
│   ├── Leave/                 # Leave management
│   ├── Penalty/               # Penalty system
│   └── Report/                # Reporting components
├── Models/                    # Eloquent Models
├── Policies/                  # Authorization policies
├── Services/                  # Business logic services
└── Helpers/                   # Global helper functions
```

### Naming Conventions
- **Models**: PascalCase (User, Schedule, Penalty)
- **Controllers**: PascalCase + "Controller" suffix
- **Livewire Components**: PascalCase (ScheduleCalendar, UserManagement)
- **Views**: kebab-case (schedule-calendar.blade.php)
- **Routes**: kebab-case (schedule.calendar)
- **Variables**: camelCase ($userName, $scheduleData)
- **Constants**: UPPER_SNAKE_CASE (MAX_SCHEDULE_DAYS)

### Database Standards
- **Table Names**: snake_case, plural (users, schedules, penalties)
- **Foreign Keys**: {table}_id (user_id, schedule_id)
- **Timestamps**: created_at, updated_at (default Laravel)
- **Soft Deletes**: deleted_at (use SoftDeletes trait)
- **Indexes**: Add indexes on frequently queried columns
- **Migrations**: Use descriptive names, include up() and down()

## 🔐 Security Standards

### Authentication & Authorization
- Use Laravel Breeze for authentication
- Implement RBAC with Spatie Permission package
- **Canonical Roles**: Super Admin, Ketua, Wakil Ketua, BPH, Anggota
- Always validate permissions in controllers and Livewire components
- Use policy classes for model authorization

### Input Validation
- Validate all user inputs using Laravel Form Request
- Use Laravel's built-in validation rules
- Sanitize and escape outputs in Blade templates
- Implement CSRF protection on all forms

### Data Protection
- Use HTTPS in production
- Implement rate limiting on sensitive routes
- Log authentication attempts and security events
- Never commit sensitive data to version control

## 📝 Code Quality Standards

### PHP Standards
- Follow PSR-12 coding standards
- Use Laravel Pint for code formatting
- Maximum line length: 120 characters
- Use type hints wherever possible
- Document complex logic with comments

### JavaScript Standards
- Use modern ES6+ syntax
- Prefer Alpine.js for interactive components
- Keep JavaScript minimal and focused
- Use proper error handling

### CSS Standards
- Use TailwindCSS utility classes
- Avoid custom CSS when possible
- Use responsive design patterns
- Follow mobile-first approach

## 🧪 Testing Standards

### Test Structure
- **Unit Tests**: Test individual methods and classes
- **Feature Tests**: Test HTTP endpoints and user flows
- **Browser Tests**: Test JavaScript interactions (if needed)
- Maintain minimum 80% code coverage

### Testing Best Practices
- Use descriptive test method names
- Test both positive and negative scenarios
- Use factories for test data
- Mock external dependencies
- Test authorization and permissions

## 🚀 Deployment Standards

### Environment Configuration
- Use environment-specific .env files
- Never commit .env files to version control
- Use Laravel's configuration caching in production
- Set appropriate file permissions

### Deployment Process
1. Run `composer install --no-dev --optimize-autoloader`
2. Run `npm ci && npm run build`
3. Run `php artisan config:cache`
4. Run `php artisan route:cache`
5. Run `php artisan view:cache`
6. Run `php artisan migrate --force`

### Performance Optimization
- Enable query caching where appropriate
- Use eager loading to prevent N+1 problems
- Implement proper indexing
- Use Laravel's built-in caching mechanisms
- Optimize images and assets

## 📚 Documentation Standards

### Code Documentation
- Use PHPDoc blocks for all classes and methods
- Document complex business logic
- Include parameter and return types
- Add examples for non-obvious functionality

### API Documentation
- Document all API endpoints
- Include request/response examples
- Document authentication requirements
- Use OpenAPI/Swagger specifications

### User Documentation
- Keep user guides simple and clear
- Include screenshots for complex workflows
- Provide troubleshooting guides
- Document all features and settings

## 🔄 Git Workflow Standards

### Branch Naming
- `main` - Production-ready code
- `develop` - Integration branch
- `feature/feature-name` - New features
- `bugfix/bug-description` - Bug fixes
- `hotfix/urgent-fix` - Critical fixes

### Commit Messages
- Use conventional commit format
- Examples:
  - `feat: add schedule generation feature`
  - `fix: resolve permission validation issue`
  - `docs: update deployment guide`
  - `refactor: optimize schedule queries`

### Pull Request Standards
- Create descriptive PR titles and descriptions
- Include screenshots for UI changes
- Add tests for new functionality
- Ensure all tests pass
- Request code review before merging

## 🛠️ Development Setup

### Prerequisites
- PHP 8.2+
- Composer 2.0+
- Node.js 18+
- MySQL 8.0+
- Git 2.30+

### Local Development
1. Clone repository
2. Run `composer install`
3. Copy `.env.example` to `.env`
4. Configure database settings
5. Run `php artisan key:generate`
6. Run `php artisan migrate`
7. Run `php artisan db:seed`
8. Run `npm install`
9. Run `npm run dev`
10. Run `php artisan serve`

### Development Tools
- **IDE**: VS Code with Laravel extensions
- **Database**: MySQL Workbench or TablePlus
- **API Testing**: Postman or Insomnia
- **Version Control**: Git with GitHub Desktop (optional)

## 📊 Monitoring & Logging

### Application Monitoring
- Use Laravel Telescope for local debugging
- Implement error tracking in production
- Monitor application performance
- Set up uptime monitoring

### Logging Standards
- Use appropriate log levels (DEBUG, INFO, WARNING, ERROR)
- Include context information in logs
- Log security events separately
- Implement log rotation for production

## 🎨 UI/UX Standards

### Design Principles
- Follow Material Design guidelines
- Use consistent color scheme
- Implement responsive design
- Ensure accessibility compliance

### Component Standards
- Use reusable Blade components
- Implement proper loading states
- Add appropriate error messages
- Include hover and focus states

## 📈 Performance Standards

### Response Time Targets
- API responses: < 200ms
- Page loads: < 2 seconds
- Database queries: < 100ms
- File uploads: progress indicators

### Optimization Techniques
- Implement proper caching strategies
- Use database query optimization
- Optimize asset loading
- Implement lazy loading where appropriate

---

## 🤝 Contributing Guidelines

1. Fork the repository
2. Create a feature branch
3. Make your changes following standards
4. Add appropriate tests
5. Submit a pull request
6. Address review feedback
7. Merge to develop branch

For detailed setup instructions, see [docs/QUICK_START.md](docs/QUICK_START.md).

## 📞 Support

For development questions or issues:
- Create an issue in GitHub
- Contact the development team
- Check existing documentation

---

*Last updated: 2025-11-14*
*Version: 1.0.0*
