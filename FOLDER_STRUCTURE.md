# Authorization Service - Complete Folder Structure

## Project Overview
This is a Laravel-based Authorization Service that handles user authentication, authorization, workflow management, and API gateway functionality for a microservices architecture.

## Root Directory Structure

```
Authorisation-Service/
├── app/                          # Core application logic
├── bootstrap/                    # Application bootstrapping files
├── config/                       # Configuration files
├── database/                     # Database migrations, factories, seeders
├── public/                       # Public assets and entry point
├── resources/                    # Views, assets, language files
├── routes/                       # Route definitions
├── storage/                      # File storage and logs
├── tests/                        # Test files
├── vendor/                       # Composer dependencies
├── .env                          # Environment configuration
├── .env.example                  # Environment template
├── artisan                       # Laravel command-line interface
├── composer.json                 # PHP dependencies
├── package.json                  # Node.js dependencies
├── phpunit.xml                   # PHPUnit configuration
├── README.md                     # Project documentation
└── vite.config.js               # Vite build configuration
```

## Detailed Directory Structure

### `/app` - Application Core
```
app/
├── BLL/                          # Business Logic Layer
│   ├── ApiGatewayBll.php        # API Gateway business logic
│   ├── AuthorizationBll.php     # Authorization business logic
│   └── MicroserviceBll.php      # Microservice business logic
│
├── Console/                      # Artisan commands
│   └── Kernel.php               # Console kernel
│
├── Exceptions/                   # Exception handling
│   └── Handler.php              # Global exception handler
│
├── Http/                        # HTTP layer
│   ├── Controllers/             # Request controllers
│   │   ├── Api/                 # API-specific controllers
│   │   │   ├── ApiController.php
│   │   │   ├── ApiRoleController.php
│   │   │   ├── ApiRoleMapController.php
│   │   │   └── ApiRoleUserMapController.php
│   │   │
│   │   ├── Auth/                # Authentication controllers
│   │   │   ├── CitizenController.php
│   │   │   └── UserController.php
│   │   │
│   │   ├── DocumentManagement/  # Document management
│   │   │
│   │   ├── Faq/                 # FAQ management
│   │   │   └── FaqController.php
│   │   │
│   │   ├── Landingpage/         # Landing page management
│   │   │   └── LandingPageController.php
│   │   │
│   │   ├── Menu/                # Menu management
│   │   │   ├── MenuController.php
│   │   │   ├── MenuRoleController.php
│   │   │   ├── MenuRoleMapController.php
│   │   │   └── MenuRoleUserMapController.php
│   │   │
│   │   ├── Payament/            # Payment processing
│   │   │   └── IciciPaymentController.php
│   │   │
│   │   ├── WorkflowMaster/      # Workflow management
│   │   │   ├── MasterController.php
│   │   │   ├── RoleController.php
│   │   │   ├── WardUserController.php
│   │   │   ├── WorkflowController.php
│   │   │   ├── WorkflowMap.php
│   │   │   ├── WorkflowMapController.php
│   │   │   ├── WorkflowRoleController.php
│   │   │   ├── WorkflowRoleMapController.php
│   │   │   └── WorkflowRoleUserMapController.php
│   │   │
│   │   ├── ApiGatewayController.php     # Main API gateway
│   │   ├── ApiMasterController.php      # API master management
│   │   ├── ApiUnauthController.php      # Unauthenticated API handler
│   │   ├── Controller.php               # Base controller
│   │   ├── CustomController.php         # Custom functionality
│   │   ├── EpramaanController.php       # E-Pramaan integration
│   │   ├── PermissionController.php     # Permission management
│   │   ├── ThirdPartyController.php     # Third-party integrations
│   │   ├── UlbController.php            # ULB (Urban Local Body) management
│   │   └── WcController.php             # Workflow controller
│   │
│   ├── Middleware/              # HTTP middleware
│   │   ├── ApiGatewayMiddleware.php     # API gateway middleware
│   │   ├── ApiPermission.php            # API permission middleware
│   │   ├── Authenticate.php             # Authentication middleware
│   │   ├── EncryptCookies.php           # Cookie encryption
│   │   ├── ExpireBearerToken.php        # Token expiration
│   │   ├── LogRoute.php                 # Route logging
│   │   ├── PreventRequestsDuringMaintenance.php
│   │   ├── RedirectIfAuthenticated.php
│   │   ├── RoleApiPermissionMiddleware.php
│   │   ├── SecureHeaders.php            # Security headers
│   │   ├── TrimStrings.php              # String trimming
│   │   ├── TrustHosts.php               # Trusted hosts
│   │   ├── TrustProxies.php             # Trusted proxies
│   │   ├── ValidateSignature.php        # Signature validation
│   │   └── VerifyCsrfToken.php          # CSRF protection
│   │
│   ├── Requests/                # Form request validation
│   │   ├── Auth/                # Authentication requests
│   │   │   ├── AuthUserRequest.php
│   │   │   ├── ChangePassRequest.php
│   │   │   └── OtpChangePass.php
│   │   ├── RequestSendOtp.php
│   │   └── RequestVerifyOtp.php
│   │
│   └── Kernel.php               # HTTP kernel
│
├── MicroServices/               # Microservice integrations
│   └── DocUpload.php           # Document upload service
│
├── Models/                      # Eloquent models
│   ├── Api/                     # API-related models
│   │   ├── ApiMaster.php
│   │   ├── ApiRegistry.php
│   │   ├── ApiRole.php
│   │   ├── ApiRolemap.php
│   │   └── ApiRoleusermap.php
│   │
│   ├── Auth/                    # Authentication models
│   │   ├── ActiveCitizen.php
│   │   ├── ActiveCitizenUndercare.php
│   │   ├── LogActiveCitizenUndercare.php
│   │   └── User.php
│   │
│   ├── DocumentManagement/      # Document management models
│   │
│   ├── Landingpage/            # Landing page models
│   │   ├── Scheme.php
│   │   └── SchemeType.php
│   │
│   ├── Menu/                   # Menu system models
│   │   ├── Menu.php
│   │   ├── MenuMaster.php
│   │   ├── MenuRole.php
│   │   ├── MenuRolemap.php
│   │   └── MenuRoleusermap.php
│   │
│   ├── Notification/           # Notification models
│   │   ├── MirrorUserNotification.php
│   │   └── UserNotification.php
│   │
│   ├── Workflows/              # Workflow models
│   │   ├── WfMaster.php
│   │   ├── WfRole.php
│   │   ├── WfRoleusermap.php
│   │   ├── WfWardUser.php
│   │   ├── WfWorkflow.php
│   │   └── WfWorkflowrolemap.php
│   │
│   └── [Various other models]  # Additional system models
│       ├── ActionMaster.php
│       ├── ApiCategory.php
│       ├── ApiScreenMapping.php
│       ├── BlogPost.php
│       ├── CustomDetail.php
│       ├── DeveloperList.php
│       ├── DistrictMaster.php
│       ├── EpramaanLogin.php
│       ├── EPramanExistCheck.php
│       ├── Faq.php
│       ├── IdGenerationParam.php
│       ├── MCity.php
│       ├── MenuApiMap.php
│       ├── ModuleMaster.php
│       ├── OtpRequest.php
│       ├── PasswordResetOtpToken.php
│       ├── QuickAccessMaster.php
│       ├── QuickaccessUserMap.php
│       ├── RoleApiMap.php
│       ├── ServiceMapping.php
│       ├── ServiceMaster.php
│       ├── TcTracking.php
│       ├── UlbMaster.php
│       ├── UlbModulePermission.php
│       ├── UlbNewWardmap.php
│       ├── UlbService.php
│       ├── UlbWardMaster.php
│       ├── UserApiExclude.php
│       ├── UserLoginDetail.php
│       └── ZoneMaster.php
│
├── Pipelines/                  # Query pipelines
│   ├── Citizen/               # Citizen search pipelines
│   │   ├── CitizenSearchByEmail.php
│   │   └── CitizenSearchByMobile.php
│   │
│   ├── Otp/                   # OTP search pipelines
│   │   ├── SearchByEmail.php
│   │   ├── SearchByMobile.php
│   │   ├── SearchByOtp.php
│   │   ├── SearchByOtpType.php
│   │   └── SearchByUserType.php
│   │
│   └── User/                  # User search pipelines
│       ├── SearchByEmail.php
│       ├── SearchByMobile.php
│       ├── SearchByName.php
│       └── SearchByRole.php
│
├── Providers/                 # Service providers
│   ├── AppServiceProvider.php
│   ├── AuthServiceProvider.php
│   ├── BroadcastServiceProvider.php
│   ├── EventServiceProvider.php
│   ├── RepositoryServiceProvider.php
│   ├── RouteServiceProvider.php
│   └── TelescopeServiceProvider.php
│
├── Repository/                # Repository pattern implementation
│   └── WorkflowMaster/       # Workflow repositories
│       ├── Concrete/         # Concrete implementations
│       │   ├── WorkflowMap.php
│       │   ├── WorkflowRoleRepository.php
│       │   └── WorkflowRoleUserMapRepository.php
│       │
│       └── Interface/        # Repository interfaces
│           ├── iWorkflowMapRepository.php
│           ├── iWorkflowRoleRepository.php
│           └── iWorkflowRoleUserMapRepository.php
│
├── Traits/                   # Reusable traits
│   ├── Validate/            # Validation traits
│   │   └── ValidateTrait.php
│   │
│   ├── Workflow/            # Workflow traits
│   │   └── Workflow.php
│   │
│   └── Auth.php             # Authentication trait
│
└── Helper Files             # Global helper functions
    ├── Helper.php           # General helper functions
    ├── SmsHelper.php        # SMS functionality
    └── WhatsaapHelper.php   # WhatsApp integration
```

### `/config` - Configuration Files
```
config/
├── apiPermission.php        # API permission configuration
├── app.php                  # Application configuration
├── auth.php                 # Authentication configuration
├── broadcasting.php         # Broadcasting configuration
├── cache.php                # Cache configuration
├── constants.php            # Application constants ⭐
├── cors.php                 # CORS configuration
├── database.php             # Database configuration
├── epramaan.php             # E-Pramaan configuration
├── filesystems.php          # Filesystem configuration
├── hashing.php              # Hashing configuration
├── logging.php              # Logging configuration
├── mail.php                 # Mail configuration
├── queue.php                # Queue configuration
├── sanctum.php              # Laravel Sanctum configuration
├── services.php             # Third-party services
├── session.php              # Session configuration
├── telescope.php            # Laravel Telescope configuration
└── view.php                 # View configuration
```

### `/database` - Database Layer
```
database/
├── factories/               # Model factories
│   └── UserFactory.php
│
├── migrations/              # Database migrations
│   ├── 2014_10_12_000000_create_users_table.php
│   ├── 2014_10_12_100000_create_password_reset_tokens_table.php
│   ├── 2019_08_19_000000_create_failed_jobs_table.php
│   └── 2019_12_14_000001_create_personal_access_tokens_table.php
│
├── seeders/                 # Database seeders
│   └── DatabaseSeeder.php
│
└── .gitignore
```

### `/public` - Public Assets
```
public/
├── ModuleIcon/              # Module icons
├── Uploads/                 # File uploads
├── vendor/                  # Published vendor assets
├── .htaccess               # Apache configuration
├── favicon.ico             # Site favicon
├── index.php               # Application entry point
└── robots.txt              # Search engine directives
```

### `/resources` - Frontend Resources
```
resources/
├── css/                    # CSS files
├── js/                     # JavaScript files
└── views/                  # Blade templates
```

### `/routes` - Route Definitions
```
routes/
├── api.php                 # API routes ⭐
├── channels.php            # Broadcast channels
├── console.php             # Console commands
└── web.php                 # Web routes
```

### `/storage` - File Storage
```
storage/
├── app/                    # Application files
├── framework/              # Framework files
├── logs/                   # Application logs
└── Screenshot (1).png      # Temporary files
```

### `/tests` - Test Suite
```
tests/
├── Feature/                # Feature tests
├── Unit/                   # Unit tests
├── CreatesApplication.php  # Test application creation
└── TestCase.php           # Base test case
```

## Key Relationships and Interconnections

### 1. **Authentication & Authorization Flow**
```
User Request → Middleware (auth:sanctum) → Controllers → BLL → Models → Database
```

### 2. **API Gateway Architecture**
```
External Request → ApiGatewayMiddleware → ApiGatewayController → Microservice APIs
```

### 3. **Workflow Management System**
```
WorkflowMaster → WfWorkflow → WfRole → WfRoleusermap → WfWardUser
```

### 4. **Menu & Permission System**
```
Menu → MenuRole → MenuRolemap → MenuRoleusermap → User Permissions
```

### 5. **API Permission System**
```
ApiMaster → ApiRole → ApiRolemap → ApiRoleusermap → API Access Control
```

## Module Integration Points

### **Core Modules (from constants.php)**
- **Property Module** (ID: 1)
- **Water Module** (ID: 2) 
- **Trade Module** (ID: 3)
- **SWM Module** (ID: 4)
- **Advertisement Module** (ID: 5)
- **Water Tanker Module** (ID: 11)
- **Legal Module** (ID: 25)

### **Captcha-Enabled Modules**
- Legal (25), LAMS (21), Fines (14), RIG (15), Water Tanker (11)
- PTMS (18), Finance Commission (20), HRMS (30), Parking (19)
- Procurement (17), Water (2), Market Advertisement (5)

## Technology Stack

### **Backend**
- **Framework**: Laravel 10.x
- **PHP Version**: ^8.1
- **Authentication**: Laravel Sanctum
- **Monitoring**: Laravel Telescope
- **Caching**: Redis (Predis)
- **JWT**: Web Token JWT libraries

### **Frontend Build**
- **Build Tool**: Vite
- **Package Manager**: npm
- **JavaScript**: ES6+ modules

### **Key Dependencies**
- **HTTP Client**: Guzzle HTTP
- **Logging**: Laravel Log Viewer
- **JWT Processing**: Web Token JWT suite
- **Testing**: PHPUnit, Mockery

## Environment Configuration

### **Key Environment Variables**
```env
MICROSERVICES_APIS=          # Microservice API endpoints
DOC_URL=                     # Document service URL
DMS_URL=                     # Document management URL
WHATSAPP_TOKEN=              # WhatsApp API token
SMS_USER_NAME=               # SMS service credentials
EPRAMAAN_CLIENT_ID=          # E-Pramaan integration
FRONTEND_URL=                # Frontend application URL
```

## Security Features

### **Middleware Stack**
- **Authentication**: Sanctum token-based auth
- **API Permissions**: Role-based API access
- **CSRF Protection**: Token validation
- **Secure Headers**: Security header injection
- **Rate Limiting**: Request throttling
- **Input Validation**: Request sanitization

### **Authorization Layers**
1. **User Authentication** (Sanctum)
2. **Role-Based Access** (Workflow roles)
3. **API Permissions** (Endpoint-level control)
4. **Menu Permissions** (UI access control)
5. **Module Permissions** (Feature-level access)

This authorization service acts as the central authentication and authorization hub for a larger microservices ecosystem, managing user access, API permissions, and workflow orchestration across multiple municipal service modules.