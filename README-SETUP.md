# PATF - Proof and Trust Framework (V2)
## NIS2-Compliant AI-Powered Vendor Risk Assessment Platform

### 🎉 Phase 1 Complete: Foundation Setup

This document tracks the migration progress from Laravel 8 to Laravel 12 + Filament 5 with AI capabilities.

---

## ✅ Completed Steps

### 1. Project Initialization
- ✅ Laravel 12 project created
- ✅ Git repository initialized
- ✅ Modern PHP 8.3 configuration

### 2. Dependencies Installed

#### Core Framework
- ✅ **Filament 5.1.3** - Modern admin panel framework
- ✅ **Livewire 4.1.2** - Reactive frontend components
- ✅ **Laravel 12** - Latest framework version

#### AI & Analysis
- ✅ **OpenAI PHP Client 0.18.0** - For GPT-4 and GPT-4 Vision integration
- ✅ Guzzle HTTP Client - API communication

#### Payments & Subscriptions
- ✅ **Laravel Cashier 16.2.0** - Stripe integration
- ✅ **Stripe PHP 17.6.0** - Payment processing
- ✅ **Money PHP 4.8.0** - Currency handling

#### Queue & Monitoring
- ✅ **Laravel Horizon 5.43.0** - Queue monitoring dashboard
- ✅ Redis support configured

#### Document Processing
- ✅ **Google Cloud Vision** - OCR for images
- ✅ **Smalot PDF Parser** - PDF text extraction
- ✅ **Spatie PDF to Text** - Alternative PDF parsing
- ✅ **PHPOffice PHPWord** - DOCX processing
- ✅ **ZipStream PHP** - File export functionality

#### Reporting & Exports
- ✅ **Laravel Excel (Maatwebsite)** - Excel report generation
- ✅ **Barryvdh DomPDF** - PDF generation
- ✅ **Spatie Activity Log** - Audit trails

### 3. Filament Panels Configured

Two separate admin interfaces have been created:

#### Admin Panel (`/admin`)
- **Purpose:** Super admin management
- **Color Theme:** Amber
- **Features:**
  - User management (customers/testers)
  - Package/subscription management
  - Questionnaire template creation
  - Keyword configuration
  - AI settings & prompts
  - NIS2 compliance templates
  - System-wide reports

#### Customer Panel (`/app`)
- **Purpose:** Customer/tester interface
- **Color Theme:** Blue
- **Features:**
  - Vendor management & classification
  - Questionnaire sending
  - AI-powered reports
  - Mitigation plan tracking
  - NIS2 compliance dashboard
  - Supply chain mapping
  - Incident tracking

---

## 📋 Next Steps

### Phase 2: Database Schema (In Progress)

Need to create migrations for:
1. **vendors** - Vendor registry with risk classification
2. **vendor_classifications** - Classification history & approval workflow
3. **questionnaire_templates** - High/Medium/Low risk templates (32/20/10 questions)
4. **ai_analyses** - GPT-4 analysis results
5. **ai_answer_analyses** - Per-answer AI evaluation
6. **mitigation_plans** - AI-generated remediation actions
7. **vendor_incidents** - Security incident tracking
8. **vendor_reassessments** - Scheduled re-evaluations
9. **vendor_dependencies** - Supply chain mapping

### Phase 3: AI Services

Create AI integration services:
- **AiAnalysisService** - Main orchestrator
- **OpenAiService** - API wrapper
- **PromptTemplates** - Structured prompts for:
  - Answer analysis
  - Evidence extraction
  - Risk assessment
  - Mitigation plan generation

### Phase 4: Core Features

1. **Vendor Classification System**
   - Guided questionnaire (8-10 questions)
   - Manual classification option
   - Approval workflow (optional)

2. **Dynamic Questionnaires**
   - High-risk: 32 questions (comprehensive)
   - Medium-risk: 20 questions (core)
   - Low-risk: 10 questions (essential)

3. **AI-Powered Analysis**
   - GPT-4 for answer evaluation
   - GPT-4 Vision for document/image analysis
   - Confidence scoring
   - Risk indicator detection

4. **NIS2 Compliance Features**
   - Compliance dashboard
   - Supply chain risk mapping
   - Incident response module
   - Reassessment scheduling

---

## 🔧 Configuration Required

### Environment Variables

Create `.env` file with:

```bash
# Application
APP_NAME="PATF - Vendor Risk Assessment"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=patf
DB_USERNAME=root
DB_PASSWORD=

# OpenAI Configuration
OPENAI_API_KEY=sk-proj-...
OPENAI_MODEL=gpt-4-turbo-preview
OPENAI_VISION_MODEL=gpt-4-vision-preview
OPENAI_MAX_TOKENS=4096
OPENAI_TEMPERATURE=0.3

# AI Cost Limits (USD per month, per package tier)
AI_COST_LIMIT_FREE=0
AI_COST_LIMIT_BASIC=50
AI_COST_LIMIT_PRO=200
AI_COST_LIMIT_ENTERPRISE=1000

# Stripe
STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
STRIPE_WEBHOOK_SECRET=whsec_...

# Google Cloud (OCR Fallback)
GOOGLE_CLOUD_PROJECT_ID=
GOOGLE_CLOUD_KEY_FILE=storage/google_acc.json

# Queue & Cache
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=noreply@patf.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Run Setup Commands

```bash
# Generate application key
php artisan key:generate

# Run migrations (once created)
php artisan migrate

# Publish Cashier migrations
php artisan vendor:publish --tag=cashier-migrations

# Install Horizon
php artisan horizon:install

# Publish Activity Log migrations
php artisan vendor:publish --provider="Spatie\Activitylog\ActivitylogServiceProvider" --tag="activitylog-migrations"
```

---

## 🏗️ Directory Structure

```
app/
├── Filament/
│   ├── Resources/          # Admin resources
│   │   ├── UserResource.php
│   │   ├── PackageResource.php
│   │   └── QuestionnaireTemplateResource.php
│   ├── Pages/              # Admin custom pages
│   ├── Widgets/            # Admin widgets
│   └── Customer/           # Customer panel
│       ├── Resources/      # Vendor, Report resources
│       ├── Pages/          # NIS2 Dashboard, Classification
│       └── Widgets/        # Customer widgets
├── Services/
│   ├── AI/
│   │   ├── AiAnalysisService.php
│   │   ├── OpenAiService.php
│   │   ├── Prompts/
│   │   │   ├── AnswerAnalysisPrompt.php
│   │   │   ├── EvidenceExtractionPrompt.php
│   │   │   ├── RiskAssessmentPrompt.php
│   │   │   └── MitigationPlanPrompt.php
│   │   ├── Analyzers/
│   │   └── CostTracker.php
│   ├── VendorClassificationService.php
│   └── DocumentProcessing/
├── Jobs/
│   ├── ProcessQuestionnaireWithAI.php
│   └── SendVendorQuestionnaire.php
└── Models/
    ├── Vendor.php
    ├── VendorClassification.php
    ├── QuestionnaireTemplate.php
    ├── AiAnalysis.php
    └── MitigationPlan.php
```

---

## 📊 Technology Stack

| Component | Version | Purpose |
|-----------|---------|---------|
| Laravel | 12.x | Core framework |
| PHP | 8.3+ | Runtime |
| Filament | 5.1.3 | Admin panels |
| Livewire | 4.1.2 | Reactive UI |
| OpenAI | GPT-4 Turbo | AI analysis |
| GPT-4 Vision | Latest | Document analysis |
| Stripe | v17.6.0 | Payments |
| Cashier | v16.2.0 | Subscriptions |
| Horizon | v5.43.0 | Queue monitoring |
| MySQL | 8.0+ | Database |
| Redis | 7.0+ | Cache & queues |

---

## 🎯 Key Features to Implement

### 1. Vendor Classification (Week 1-2)
- [ ] Guided questionnaire (8-10 questions)
- [ ] Manual classification with justification
- [ ] Risk score calculation algorithm
- [ ] Optional approval workflow

### 2. Questionnaire Templates (Week 2-3)
- [ ] High-risk template (32 questions)
- [ ] Medium-risk template (20 questions)
- [ ] Low-risk template (10 questions)
- [ ] NIS2 requirement mapping
- [ ] Question bank management

### 3. AI Integration (Week 3-5)
- [ ] OpenAI service wrapper
- [ ] Prompt engineering
- [ ] Answer analysis
- [ ] Evidence extraction (GPT-4 Vision)
- [ ] Risk assessment
- [ ] Mitigation plan generation
- [ ] Cost tracking & limits

### 4. NIS2 Compliance (Week 6-7)
- [ ] Compliance dashboard
- [ ] Supply chain risk mapping
- [ ] Incident tracking
- [ ] Reassessment scheduling
- [ ] Audit trail (Activity Log)

### 5. Reporting (Week 8)
- [ ] AI-generated reports
- [ ] PDF export
- [ ] Excel export
- [ ] White-label customization

---

## 📈 Success Metrics

- **AI Accuracy:** Target >90% (vs 60-70% keyword matching)
- **Processing Time:** <2 minutes per questionnaire
- **Cost per Assessment:** <$5 (OpenAI API)
- **NIS2 Coverage:** 100% of 18 requirements mapped
- **System Uptime:** >99.9%

---

## 🚀 Quick Start (Development)

```bash
# 1. Install dependencies (already done)
composer install

# 2. Configure environment
cp .env.example .env
php artisan key:generate

# 3. Set up database
php artisan migrate

# 4. Seed initial data
php artisan db:seed

# 5. Start development server
php artisan serve

# 6. Start queue worker (separate terminal)
php artisan horizon

# 7. Access panels
# Admin: http://localhost:8000/admin
# Customer: http://localhost:8000/app
```

---

## 📝 Notes

- This is a **complete rewrite** with modern architecture
- **Zero dependency** on old Laravel 8 codebase
- **AI-first** approach replaces keyword matching
- **NIS2 compliant** from the ground up
- **Scalable** architecture for enterprise use

---

**Last Updated:** 2026-02-03
**Version:** 2.0-alpha
**Status:** Foundation Complete ✅
