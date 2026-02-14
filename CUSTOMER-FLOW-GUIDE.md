# 🎯 PATF Customer Registration & Workflow Guide

**Complete Step-by-Step Guide for Testing the SaaS Platform**

---

## 🚀 Quick Start

### Step 1: Start the Development Server

```bash
cd /Users/stefanrakic/Projects/patf
php artisan serve
```

**Server will start at:** `http://localhost:8000`

---

## 👤 Customer Registration Flow

### Step 2: Register as a New Customer

1. **Go to the Customer Panel:**
   - URL: `http://localhost:8000/app`
   - You'll see the login page

2. **Click "Register" (or navigate to registration)**
   - URL: `http://localhost:8000/app/register`

3. **Fill in Registration Form:**
   ```
   Name: John Doe
   Email: customer@example.com
   Password: password123
   Confirm Password: password123
   ```

4. **Submit Registration**
   - ✅ Account automatically created
   - ✅ Assigned "Free Trial" package (3 assessments)
   - ✅ Role set to "tester"
   - ✅ Status set to "trial"

5. **Email Verification** (Optional for testing)
   - In production, you'd verify email
   - For testing, it's optional

6. **Automatic Login**
   - After registration, you're automatically logged in
   - Redirected to Customer Dashboard

---

## 🏢 Customer Dashboard Overview

### What You'll See:

**Navigation Menu:**
- 📊 **Dashboard** - Overview of your account
- 🏢 **My Vendors** - Manage vendor assessments
- 📋 **Questionnaires** - View sent/completed assessments
- 👤 **Profile** - Edit your account details

**Vendor Card Badge:**
- Shows: `0/3` (used/available assessments)
- Color coding:
  - 🟢 Green: <70% used
  - 🟠 Orange: 70-89% used
  - 🔴 Red: ≥90% used

---

## 🏢 Adding Your First Vendor

### Step 3: Create a Vendor

1. **Navigate to "My Vendors"**
   - Click on "My Vendors" in the sidebar
   - Or go to: `http://localhost:8000/app/vendors`

2. **Click "Create" Button** (top right)

3. **Fill in Vendor Information Form:**

   **Vendor Information Section:**
   ```
   Vendor Name: Acme Cloud Services
   Industry: Cloud Computing
   Company Description: Cloud hosting and storage provider
   ```

   **Point of Contact Section:**
   ```
   Contact Name: Jane Smith
   Contact Email: jane@acmecloud.com
   ```

   **Risk Classification Section:** (Auto-populated, disabled)
   - Current Risk Level: (Not yet classified)
   - Classification Status: Pending Classification
   - Next Assessment Date: (Auto-populated after classification)
   - Active Vendor: ✅ (checked)

4. **Click "Create"**
   - ✅ Vendor created successfully
   - ✅ Automatically linked to your account
   - ✅ Status: "Pending Classification"
   - ✅ Counter updates: `1/3` assessments used

### Step 4: View Your Vendors

**Vendors List Page Features:**

**Columns Displayed:**
- 📋 **Vendor Name** - Bold, searchable
- 👤 **Contact Person** - Searchable
- 📧 **Contact Email** - Copyable
- 🏭 **Industry**
- ⚠️ **Risk Level** - Badge with color:
  - 🔴 High Risk (Red)
  - 🟠 Medium Risk (Orange)
  - 🟢 Low Risk (Green)
  - ⚪ Not Classified (Gray)
- 📊 **Status** - Badge:
  - ⚪ Pending (Gray)
  - 🟠 Pending Approval (Orange)
  - 🟢 Approved (Green)
  - 🔴 Rejected (Red)
- 📅 **Next Assessment** - Date
- ✅ **Active** - Toggle icon

**Available Filters:**
- Filter by Risk Level (High/Medium/Low)
- Filter by Status (Pending/Approved/Rejected)
- Filter by Active Status
- Show Deleted Records

**Available Actions:**
- 👁️ View vendor details
- ✏️ Edit vendor information
- 🗑️ Delete vendor (soft delete)
- 📋 Bulk actions (delete multiple)

---

## 🎯 Vendor Classification Workflow

### Step 5: Classify Your Vendor (Next Feature)

**Current Status:** Structure created, logic to be implemented

**How It Will Work:**

1. **Navigate to Vendor Edit Page**
2. **Click "Classify Vendor" Button**
3. **Choose Classification Method:**
   - **Guided Questionnaire** (8-10 questions)
     - Data access level?
     - Critical infrastructure?
     - Compliance requirements?
     - etc.
   - **Manual Classification**
     - Select risk level directly
     - Provide justification

4. **Risk Determination:**
   - **High Risk** → 32 question questionnaire
   - **Medium Risk** → 20 question questionnaire
   - **Low Risk** → 10 question questionnaire

5. **Approval Workflow** (Optional):
   - Classification pending approval
   - Approver reviews and approves/rejects

---

## 📋 Sending Questionnaires (Coming Soon)

### Step 6: Send Questionnaire to Vendor

**Will Work Like This:**

1. **Select Classified Vendor**
2. **Click "Send Questionnaire"**
3. **Email Automatically Sent:**
   ```
   To: jane@acmecloud.com
   Subject: Security Assessment Request from [Your Company]
   
   Body: 
   Dear Jane Smith,
   
   We require you to complete a security assessment questionnaire.
   Please click the link below to get started:
   
   [Unique Link]
   
   This questionnaire should take approximately 20-30 minutes.
   ```

4. **Vendor Clicks Link**
5. **Public Questionnaire Page Opens** (Livewire)
6. **Vendor Completes Questions:**
   - Multiple choice
   - Text responses
   - File uploads (evidence)
   - Progress saved automatically

7. **Vendor Submits Questionnaire**

---

## 🤖 AI Analysis Process (Coming Soon)

### Step 7: Automated AI Analysis

**After Vendor Submits:**

1. **Queue Job Triggered**
2. **AI Analysis Pipeline:**
   
   **Phase 1: Answer Analysis** (GPT-4)
   - Each answer analyzed individually
   - Evidence quality assessment
   - Risk indicators identified
   - Compliance verdict (pass/fail/partial)
   - Confidence scoring

   **Phase 2: Evidence Extraction** (GPT-4 Vision)
   - Uploaded documents/images analyzed
   - Key information extracted
   - Certifications verified
   - Security controls documented

   **Phase 3: Risk Aggregation**
   - Individual scores combined
   - Overall risk calculated
   - Risk level assigned (High/Medium/Low)

   **Phase 4: Summary Generation** (GPT-4)
   - Executive summary created
   - Key findings highlighted
   - Compliance gaps identified

   **Phase 5: Mitigation Plans** (GPT-4)
   - High-risk areas identified
   - Actionable recommendations generated
   - NIS2 references added
   - Priority levels assigned

3. **Processing Time:** <2 minutes
4. **Cost Tracking:** ~$2-5 per assessment

---

## 📊 Viewing Results

### Step 8: Review AI-Generated Report

**Report Includes:**

1. **Executive Summary**
   - Overall risk rating
   - Confidence score
   - Key findings

2. **Question-by-Question Analysis**
   - Answer evaluation
   - Evidence assessment
   - Risk indicators
   - AI reasoning

3. **Compliance Matrix**
   - NIS2 requirements mapped
   - Pass/fail status per requirement
   - Gaps identified

4. **Mitigation Plans**
   - Categorized by severity
   - Actionable recommendations
   - Due dates
   - Assignment capability

5. **Supply Chain Impact**
   - Dependency mapping
   - Cascade risk analysis

---

## 💳 Upgrading Your Subscription

### Step 9: Upgrade Package

**When You Hit Assessment Limit:**

1. **Navigate to Profile or Billing**
2. **View Current Package:**
   ```
   Free Trial
   - 3 Assessments (Used: 3/3)
   - No AI Analysis
   - Email Support
   ```

3. **Click "Upgrade"**
4. **Choose New Package:**
   
   **Basic ($49/month):**
   - 10 Assessments
   - $50 AI Budget
   - Email Support

   **Professional ($149/month):**
   - 50 Assessments
   - $200 AI Budget
   - Priority Support
   - Custom Branding
   - API Access

   **Enterprise ($499/month):**
   - Unlimited Assessments
   - $1,000 AI Budget
   - Dedicated Support
   - White-label
   - SSO

5. **Stripe Checkout** (To be integrated)
6. **Automatic Provisioning:**
   - Assessment limit updated
   - AI features enabled
   - New features unlocked

---

## 🔄 Reassessment Workflow

### Step 10: Schedule Reassessments

**Automatic Reassessment Triggers:**

1. **Time-Based:**
   - High-risk: Every 6 months
   - Medium-risk: Annually
   - Low-risk: Every 2 years

2. **Event-Based:**
   - Security incident reported
   - Contract change
   - Risk level change

3. **Manual:**
   - Customer requests reassessment
   - Compliance requirement

**Reassessment Process:**
- Email notification sent automatically
- Vendor completes questionnaire again
- New AI analysis performed
- Results compared to previous assessment
- Risk trend tracking

---

## 📈 Dashboard Widgets

### Your Dashboard Shows:

**Vendor Overview Widget:**
- Total vendors: 1
- High-risk: 0
- Medium-risk: 0
- Low-risk: 0
- Pending classification: 1

**Assessment Activity:**
- Assessments this month: 1
- Completed assessments: 0
- Pending responses: 1
- Overdue: 0

**AI Usage:**
- Monthly AI spend: $0/$0 (Free Trial)
- Assessments analyzed: 0
- Average confidence: N/A

**Compliance Status:**
- NIS2 compliant vendors: 0%
- Critical gaps: 0
- High-risk findings: 0

**Upcoming Reassessments:**
- Next 30 days: 0
- Next 90 days: 0

---

## 🎨 UI Features You'll Love

### Modern Filament Interface:

✅ **Beautiful Color-Coded Badges**
- Risk levels instantly recognizable
- Status indicators clear

✅ **Advanced Filtering**
- Multi-criteria filtering
- Search across all fields
- Toggle columns on/off

✅ **Bulk Actions**
- Select multiple vendors
- Perform actions in batch
- Time-saving operations

✅ **Responsive Design**
- Works on desktop, tablet, mobile
- Touch-friendly interface
- Adaptive layouts

✅ **Dark Mode Support** (Optional)
- Easy on the eyes
- Professional appearance

✅ **Real-Time Updates**
- Progress indicators
- Live notifications
- Dynamic content

---

## 🔒 Security Features

### What's Protected:

✅ **Data Isolation**
- You only see your vendors
- Complete multi-tenancy
- No data leakage

✅ **Authentication**
- Email verification
- Password reset
- Session management

✅ **Authorization**
- Role-based access
- Resource-level permissions
- Action-level security

✅ **Audit Trail** (Coming)
- All actions logged
- Who did what, when
- Compliance reporting

---

## 🐛 Troubleshooting

### Common Issues:

**Can't Register?**
- Clear browser cache
- Try different browser
- Check server is running

**Don't Receive Verification Email?**
- Check spam folder
- For testing, verification is optional
- Update MAIL settings in `.env`

**Can't Create Vendor?**
- Check assessment limit
- Verify you're logged in
- Ensure Free Trial package assigned

**Features Not Working?**
- Run: `php artisan optimize:clear`
- Restart server: `php artisan serve`

---

## 📞 Next Steps

### What's Coming Next (Weeks 1-4):

✅ **Classification Workflow** - Complete the guided questionnaire  
✅ **Questionnaire Sending** - Email integration  
✅ **Public Questionnaire Interface** - Livewire forms  
✅ **AI Integration** - Connect GPT-4 analysis  
✅ **Reporting** - PDF generation  

### Want to Help Test?

1. **Register multiple test accounts**
2. **Create various vendor types**
3. **Test edge cases**
4. **Report any issues**
5. **Suggest improvements**

---

## 🎯 Testing Checklist

Use this to verify everything works:

### Registration & Authentication
- [ ] Can register new account
- [ ] Receive Free Trial package automatically
- [ ] Can log in successfully
- [ ] Can access customer dashboard
- [ ] Can edit profile
- [ ] Can change password
- [ ] Can log out

### Vendor Management
- [ ] Can create new vendor
- [ ] Badge shows: 1/3
- [ ] Vendor appears in list
- [ ] Can edit vendor details
- [ ] Can view vendor details
- [ ] Can delete vendor
- [ ] Can filter vendors
- [ ] Can search vendors

### Assessment Limits
- [ ] Can create up to 3 vendors (Free Trial)
- [ ] Badge turns orange at 70% (2/3)
- [ ] Badge turns red at 90% (3/3)
- [ ] Cannot create 4th vendor
- [ ] Appropriate error message shown

### UI/UX
- [ ] Navigation menu works
- [ ] Badges display correctly
- [ ] Filters work properly
- [ ] Search is functional
- [ ] Bulk actions available
- [ ] Responsive on mobile

---

## 🎉 Summary

**You Now Have:**
✅ Full customer registration system  
✅ Automatic Free Trial assignment  
✅ Vendor management (CRUD)  
✅ Assessment limit tracking  
✅ Beautiful Filament UI  
✅ Data isolation per customer  
✅ Role-based access control  

**Ready to Test:**
1. Go to `http://localhost:8000/app/register`
2. Register as `customer@example.com`
3. Create your first vendor
4. Explore the interface!

---

**Need Help?**
- Check logs: `storage/logs/laravel.log`
- Clear cache: `php artisan optimize:clear`
- Reset database: `php artisan migrate:fresh --seed`

**Have Fun Testing! 🚀**

---

**Last Updated:** 2026-02-03  
**Version:** 2.0-alpha  
**Status:** Customer Flow Ready ✅
