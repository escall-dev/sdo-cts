# SDO CTS - Complete System Architecture

## San Pedro Division Office Complaint Tracking System

---

## System Overview Diagram

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                           SDO CTS - COMPLAINT TRACKING SYSTEM                                │
│                    San Pedro Division Office - Department of Education                       │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
                    ┌───────────────────────┴───────────────────────┐
                    ▼                                               ▼
    ┌───────────────────────────────┐              ┌───────────────────────────────┐
    │      PUBLIC INTERFACE         │              │       ADMIN INTERFACE          │
    │         (Complainant)         │              │      (Division Office)         │
    └───────────────────────────────┘              └───────────────────────────────┘
              │                                              │
    ┌─────────┴─────────┐                      ┌─────────────┴─────────────┐
    │                   │                      │                           │
    ▼                   ▼                      ▼                           ▼
┌─────────┐      ┌────────────┐        ┌────────────┐            ┌────────────────┐
│ Submit  │      │   Track    │        │   Login    │            │   Dashboard    │
│Complaint│      │  Status    │        │ (Auth)     │            │   & Manage     │
└─────────┘      └────────────┘        └────────────┘            └────────────────┘
    │                   │                      │                           │
    └─────────┬─────────┘                      └─────────────┬─────────────┘
              │                                              │
              ▼                                              ▼
┌───────────────────────────────────────────────────────────────────────────────────────────┐
│                                      DATABASE (MySQL)                                      │
│                                        sdo_cts                                            │
└───────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    PRESENTATION LAYER                                       │
├───────────────────────────────────────────────────────────────────────────────────────────────┤
│  PUBLIC PAGES                    │  ADMIN PAGES                │  API ENDPOINTS              │
│  ├─ index.php (Form)             │  ├─ index.php (Dashboard)   │  ├─ analytics.php           │
│  ├─ review.php (Review/Submit)   │  ├─ complaints.php          │  ├─ analytics-export.php    │
│  ├─ success.php (Confirmation)   │  ├─ complaint-view.php      │  ├─ complaint-action.php    │
│  ├─ track.php (Status Tracking)  │  ├─ users.php               │  ├─ update-status.php       │
│  └─ contact.php                  │  ├─ analytics.php           │  ├─ forward-complaint.php   │
│                                  │  ├─ email-logs.php          │  ├─ save-user.php           │
│                                  │  ├─ email-settings.php      │  ├─ delete-user.php         │
│                                  │  ├─ logs.php (Activity)     │  ├─ user-status.php         │
│                                  │  ├─ profile.php             │  └─ notification-count.php  │
│                                  │  └─ login.php               │                             │
└───────────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
                                            ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                     BUSINESS LOGIC LAYER                                    │
├───────────────────────────────────────────────────────────────────────────────────────────────┤
│  MODELS                                    │  SERVICES                                      │
│  ├─ Complaint.php                          │  ├─ EmailService.php                          │
│  ├─ ComplaintAdmin.php                     │  │   └─ PHPMailer SMTP Integration            │
│  ├─ AdminUser.php                          │  │                                            │
│  ├─ ActivityLog.php                        │  ├─ ComplaintNotification.php                 │
│  └─ EmailLog.php                           │  │   └─ Email Templates                       │
│                                            │  │                                            │
│                                            │  └─ ComplaintFormGenerator.php                │
└───────────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
                                            ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      DATA ACCESS LAYER                                      │
├───────────────────────────────────────────────────────────────────────────────────────────────┤
│  CONFIG                          │  DATABASE (MySQL - sdo_cts)                              │
│  ├─ database.php (PDO)           │  ├─ complaints                                          │
│  ├─ admin_config.php             │  ├─ complaint_documents                                 │
│  └─ mail_config.php              │  ├─ complaint_history                                   │
│                                  │  ├─ complaint_assignments                               │
│                                  │  ├─ admin_users                                         │
│                                  │  ├─ admin_roles                                         │
│                                  │  ├─ activity_log                                        │
│                                  │  ├─ password_reset_tokens                               │
│                                  │  └─ email_logs                                          │
└───────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Public Complaint Flow

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              COMPLAINT SUBMISSION WORKFLOW                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

     ┌──────────────┐         ┌──────────────┐         ┌──────────────┐         ┌──────────────┐
     │   STEP 1     │────────▶│   STEP 2     │────────▶│   STEP 3     │────────▶│   STEP 4     │
     │ Fill Form    │         │   Upload     │         │    Review    │         │   Success    │
     │ (index.php)  │         │   Documents  │         │  (review.php)│         │(success.php) │
     └──────────────┘         └──────────────┘         └──────────────┘         └──────────────┘
           │                        │                        │                        │
           ▼                        ▼                        ▼                        ▼
   ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐    ┌──────────────────┐
   │ • Complainant    │    │ • Supporting     │    │ • Preview all    │    │ • Reference #    │
   │   Information    │    │   Documents      │    │   data entered   │    │   CTS-YYYY-XXXXX │
   │ • Person/Office  │    │ • Valid ID       │    │ • Certification  │    │ • Email sent     │
   │   Involved       │    │ • Handwritten    │    │   checkbox       │    │   to both        │
   │ • Narration      │    │   Form           │    │ • Digital/Typed  │    │   parties        │
   │ • Relief Sought  │    │                  │    │   Signature      │    │                  │
   └──────────────────┘    └──────────────────┘    └──────────────────┘    └──────────────────┘
                                   │                        │
                                   ▼                        ▼
                           ┌──────────────┐         ┌─────────────────┐
                           │uploads/temp/ │────────▶│assets/uploads/  │
                           │(temporary)   │         │images/ + docs/  │
                           └──────────────┘         └─────────────────┘
                                                           │
                                                           ▼
                                                    ┌────────────┐
                                                    │  DATABASE  │
                                                    │ complaints │
                                                    │ documents  │
                                                    │  history   │
                                                    └────────────┘
```

---

## Complaint Status Tracking Flow

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              COMPLAINT TRACKING (track.php)                                  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                            │
                                            ▼
                              ┌─────────────────────────┐
                              │  User Enters:           │
                              │  • Reference Number     │
                              │  • Email Address        │
                              └─────────────────────────┘
                                            │
                                            ▼
                              ┌─────────────────────────┐
                              │    Complaint.track()    │
                              │   Validates both fields │
                              └─────────────────────────┘
                                            │
                    ┌───────────────────────┴───────────────────────┐
                    ▼                                               ▼
        ┌───────────────────┐                           ┌───────────────────┐
        │     NOT FOUND     │                           │       FOUND       │
        │   Display Error   │                           │   Show Details    │
        └───────────────────┘                           └───────────────────┘
                                                                │
                                                                ▼
                                                  ┌─────────────────────────┐
                                                  │   Display:              │
                                                  │   • Current Status      │
                                                  │   • Status Timeline     │
                                                  │   • Assigned Unit       │
                                                  │   • Last Updated        │
                                                  └─────────────────────────┘
```

---

## Admin Authentication Flow

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                ADMIN AUTHENTICATION SYSTEM                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │     admin/login.php     │
                              └─────────────────────────┘
                                          │
                          ┌───────────────┴───────────────┐
                          ▼                               ▼
              ┌───────────────────┐           ┌───────────────────┐
              │  Email + Password │           │   Google OAuth    │
              │      Login        │           │      SSO          │
              └───────────────────┘           └───────────────────┘
                          │                               │
                          ▼                               ▼
              ┌───────────────────┐           ┌───────────────────┐
              │AdminUser::        │           │AdminUser::        │
              │ authenticate()    │           │authenticateGoogle()│
              └───────────────────┘           └───────────────────┘
                          │                               │
                          └───────────────┬───────────────┘
                                          ▼
                              ┌─────────────────────────┐
                              │   Password Verification │
                              │   password_verify()     │
                              └─────────────────────────┘
                                          │
                    ┌─────────────────────┴─────────────────────┐
                    ▼                                           ▼
        ┌───────────────────┐                       ┌───────────────────┐
        │   AUTH FAILED     │                       │   AUTH SUCCESS    │
        │  Display Error    │                       │                   │
        └───────────────────┘                       └───────────────────┘
                                                            │
                                                            ▼
                                              ┌─────────────────────────┐
                                              │   Create Session        │
                                              │  $_SESSION['admin_user']│
                                              │  • id, email, name      │
                                              │  • role_id, unit        │
                                              │  • permissions          │
                                              └─────────────────────────┘
                                                            │
                                                            ▼
                                              ┌─────────────────────────┐
                                              │  ActivityLog::log()     │
                                              │  action_type: 'login'   │
                                              └─────────────────────────┘
                                                            │
                                                            ▼
                                              ┌─────────────────────────┐
                                              │  Redirect to Dashboard  │
                                              │   admin/index.php       │
                                              └─────────────────────────┘
```

---

## Admin Dashboard & Complaint Management

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                             ADMIN DASHBOARD (admin/index.php)                                │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
              ┌───────────────────────────┼───────────────────────────┐
              ▼                           ▼                           ▼
    ┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
    │   STATISTICS     │       │  RECENT ACTIVITY │       │   QUICK STATS    │
    └──────────────────┘       └──────────────────┘       └──────────────────┘
              │                           │                           │
              ▼                           ▼                           ▼
    ┌──────────────────┐       ┌──────────────────┐       ┌──────────────────┐
    │ ComplaintAdmin:: │       │ ActivityLog::    │       │ ComplaintAdmin:: │
    │ getStatistics()  │       │ getRecentActivity()      │ getRecent()      │
    └──────────────────┘       └──────────────────┘       └──────────────────┘
              │
              ▼
    ┌─────────────────────────────────────────────────────────────────────────┐
    │                         STATISTICS CARDS                                 │
    ├─────────────────────────────────────────────────────────────────────────┤
    │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐       │
    │  │ Total   │  │ Pending │  │Accepted │  │   In    │  │Resolved │       │
    │  │Complaints│  │         │  │         │  │Progress │  │         │       │
    │  └─────────┘  └─────────┘  └─────────┘  └─────────┘  └─────────┘       │
    │                                                                         │
    │  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────┐                    │
    │  │Returned │  │ Closed  │  │  This   │  │ Today's │                    │
    │  │         │  │         │  │  Week   │  │   New   │                    │
    │  └─────────┘  └─────────┘  └─────────┘  └─────────┘                    │
    └─────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                          COMPLAINT LIST (admin/complaints.php)                               │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │   FILTERS & SEARCH      │
                              │  • Status               │
                              │  • Date Range           │
                              │  • Unit (OSDS/SGOD/CID) │
                              │  • Search (ref/name)    │
                              └─────────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │ ComplaintAdmin::        │
                              │   getAll($filters,      │
                              │   $page, $perPage)      │
                              └─────────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │   PAGINATED TABLE       │
                              │  • Reference Number     │
                              │  • Complainant          │
                              │  • Status Badge         │
                              │  • Date Submitted       │
                              │  • Assigned Unit        │
                              │  • Actions Button       │
                              └─────────────────────────┘
```

---

## Complaint Processing Workflow

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                   COMPLAINT STATUS WORKFLOW (complaint-view.php)                             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐     ┌──────────┐
    │ PENDING  │────▶│ ACCEPTED │────▶│IN_PROGRESS────▶│ RESOLVED │────▶│  CLOSED  │
    └──────────┘     └──────────┘     └──────────┘     └──────────┘     └──────────┘
         │                │                │                │
         │                │                │                ▼
         │                │                │         ┌──────────────┐
         │                │                │         │ Email Sent   │
         │                │                │         │ to Complainant│
         │                │                │         └──────────────┘
         │                │                │
         ▼                ▼                ▼
    ┌──────────┐     ┌──────────────────────────┐
    │ RETURNED │     │     FORWARD TO UNIT      │
    │(w/reason)│     │ • OSDS • SGOD • CID     │
    └──────────┘     └──────────────────────────┘
         │                      │
         ▼                      ▼
    ┌──────────────┐    ┌──────────────────────┐
    │ Email Sent   │    │complaint_assignments │
    │ with reason  │    │  table updated       │
    └──────────────┘    └──────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              ADMIN ACTIONS (API Endpoints)                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

    ┌────────────────────┐          ┌────────────────────┐
    │ complaint-action   │          │   update-status    │
    │      .php          │          │       .php         │
    ├────────────────────┤          ├────────────────────┤
    │ Actions:           │          │ Status Changes:    │
    │ • accept           │          │ • pending          │
    │ • return           │          │ • accepted         │
    │ • resolve          │          │ • in_progress      │
    │                    │          │ • resolved         │
    │                    │          │ • returned         │
    │                    │          │ • closed           │
    └────────────────────┘          └────────────────────┘
              │                              │
              └──────────────┬───────────────┘
                             ▼
              ┌─────────────────────────────┐
              │   ComplaintAdmin::          │
              │   updateStatus() / accept() │
              │   returnComplaint()         │
              └─────────────────────────────┘
                             │
                             ▼
              ┌─────────────────────────────┐
              │   complaint_history table   │
              │   Activity logged           │
              │   Email notifications       │
              └─────────────────────────────┘
```

---

## User Management System

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                             USER MANAGEMENT (admin/users.php)                                │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │     AdminUser Model     │
                              └─────────────────────────┘
                                          │
        ┌─────────────────┬───────────────┼───────────────┬─────────────────┐
        ▼                 ▼               ▼               ▼                 ▼
┌──────────────┐  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│   getAll()   │  │   create()   │ │   update()   │ │  activate()  │ │   delete()   │
│  List Users  │  │  Add User    │ │   Edit User  │ │ deactivate() │ │  Remove User │
└──────────────┘  └──────────────┘ └──────────────┘ └──────────────┘ └──────────────┘
        │                 │               │               │                 │
        ▼                 ▼               ▼               ▼                 ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      admin_users                                             │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  id │ email │ password_hash │ full_name │ role_id │ google_id │ unit │ is_active │ ...     │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      admin_roles                                             │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  id │      name       │        description         │         permissions (JSON)             │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  1  │  Super Admin    │  Full system access        │  {"all": true}                         │
│  2  │  Admin          │  Manage complaints/users   │  {"complaints": true, "users": true}   │
│  3  │  Staff          │  Process complaints        │  {"complaints": true}                  │
│  4  │  Viewer         │  View only access          │  {"complaints_view": true}             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Analytics System

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                            ANALYTICS (admin/analytics.php)                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │ ComplaintAdmin::        │
                              │ getAnalytics($filters)  │
                              └─────────────────────────┘
                                          │
        ┌─────────────────────────────────┼─────────────────────────────────┐
        ▼                                 ▼                                 ▼
┌────────────────────┐         ┌────────────────────┐         ┌────────────────────┐
│ STATUS BREAKDOWN   │         │ TREND ANALYSIS     │         │  UNIT ANALYSIS     │
│ • Pending count    │         │ • Daily/Weekly     │         │ • By OSDS/SGOD/CID │
│ • Resolved count   │         │ • Monthly trends   │         │ • Workload balance │
│ • Returned count   │         │ • Year comparisons │         │ • Processing time  │
└────────────────────┘         └────────────────────┘         └────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │  EXPORT (CSV/Excel)     │
                              │ admin/api/analytics-    │
                              │     export.php          │
                              └─────────────────────────┘
```

---

## Email Notification System

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                  EMAIL NOTIFICATION SYSTEM                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │     EmailService.php    │
                              │     (PHPMailer SMTP)    │
                              └─────────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │ ComplaintNotification   │
                              │        .php             │
                              └─────────────────────────┘
                                          │
        ┌─────────────────┬───────────────┼───────────────┬─────────────────┐
        ▼                 ▼               ▼               ▼                 ▼
┌──────────────┐  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│  Complaint   │  │  Status      │  │  Complaint   │  │   Forward    │  │  Return      │
│  Submitted   │  │  Changed     │  │  Resolved    │  │   to Unit    │  │  Complaint   │
└──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘
        │                 │               │               │                 │
        ▼                 ▼               ▼               ▼                 ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    EMAIL RECIPIENTS                                          │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  Complainant ←───── Confirmation, Status Updates, Resolution                                │
│  Admin Team  ←───── New Complaints, Forwards, Escalations                                   │
│  Unit-Specific ←─── Forward Notifications (OSDS/SGOD/CID)                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      email_logs                                              │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  id │ recipient_email │ subject │ event_type │ reference_id │ status │ error_message │...  │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                    ┌────────────────────────────────────────┐
                    │    Email Log Viewer                    │
                    │    admin/email-logs.php                │
                    ├────────────────────────────────────────┤
                    │    EmailLog Model                      │
                    │    • getAll()   - Paginated list       │
                    │    • getCount() - Total count          │
                    │    • getStatistics() - Dashboard       │
                    │    • getRecentFailed() - Errors        │
                    └────────────────────────────────────────┘
```

---

## Activity Logging System

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   ACTIVITY LOGGING                                           │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │   ActivityLog Model     │
                              │    ActivityLog.php      │
                              └─────────────────────────┘
                                          │
                                          ▼
                              ┌─────────────────────────┐
                              │       log() Method      │
                              │  Captures all actions   │
                              └─────────────────────────┘
                                          │
        ┌─────────────────────────────────┼─────────────────────────────────┐
        ▼                                 ▼                                 ▼
┌────────────────────┐         ┌────────────────────┐         ┌────────────────────┐
│  ACTION TYPES      │         │   ENTITY TYPES     │         │   METADATA         │
├────────────────────┤         ├────────────────────┤         ├────────────────────┤
│  • login           │         │  • complaint       │         │  • ip_address      │
│  • logout          │         │  • admin_user      │         │  • user_agent      │
│  • view            │         │  • settings        │         │  • old_value       │
│  • create          │         │  • email           │         │  • new_value       │
│  • update          │         │  • document        │         │  • description     │
│  • delete          │         │                    │         │                    │
│  • status_change   │         │                    │         │                    │
│  • forward         │         │                    │         │                    │
│  • accept          │         │                    │         │                    │
│  • return          │         │                    │         │                    │
└────────────────────┘         └────────────────────┘         └────────────────────┘
                                          │
                                          ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                      activity_log                                            │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│  id │ user_id │ action_type │ entity_type │ entity_id │ description │ ip_address │ ...     │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

                              ┌─────────────────────────┐
                              │  Activity Log Viewer    │
                              │    admin/logs.php       │
                              └─────────────────────────┘
```

---

## Database Schema

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                               DATABASE: sdo_cts                                              │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   complaints                                                 │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)                    INT AUTO_INCREMENT                                                │
│ reference_number           VARCHAR(20) UNIQUE - Format: CTS-YYYY-XXXXX                      │
│ referred_to                ENUM('OSDS','SGOD','CID','Others')                               │
│ referred_to_other          VARCHAR(255) - If 'Others' selected                              │
│ date_petsa                 DATETIME                                                          │
│                                                                                              │
│ -- Complainant Information --                                                               │
│ name_pangalan              VARCHAR(255)                                                      │
│ address_tirahan            TEXT                                                              │
│ contact_number             VARCHAR(20)                                                       │
│ email_address              VARCHAR(255)                                                      │
│                                                                                              │
│ -- Person/Office Involved --                                                                │
│ involved_full_name         VARCHAR(255)                                                      │
│ involved_position          VARCHAR(255)                                                      │
│ involved_address           TEXT                                                              │
│ involved_school_office_unit VARCHAR(255)                                                     │
│                                                                                              │
│ -- Complaint Details --                                                                     │
│ narration_complaint        TEXT                                                              │
│ narration_complaint_page2  TEXT                                                              │
│ desired_action_relief      TEXT                                                              │
│ certification_agreed       TINYINT(1)                                                        │
│ printed_name_pangalan      VARCHAR(255)                                                      │
│ signature_type             ENUM('digital','typed')                                           │
│ signature_data             TEXT                                                              │
│ date_signed                DATE                                                              │
│                                                                                              │
│ -- Status & Processing --                                                                   │
│ status                     ENUM('pending','accepted','in_progress','resolved','returned',   │
│                                 'closed')                                                    │
│ is_locked                  TINYINT(1) DEFAULT 1                                             │
│ accepted_at                TIMESTAMP NULL                                                    │
│ accepted_by                INT (FK → admin_users)                                           │
│ returned_at                TIMESTAMP NULL                                                    │
│ returned_by                INT (FK → admin_users)                                           │
│ return_reason              TEXT                                                              │
│ assigned_unit              VARCHAR(50)                                                       │
│ handled_by                 INT (FK → admin_users)                                           │
│ created_at                 TIMESTAMP                                                         │
│ updated_at                 TIMESTAMP                                                         │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
        │
        │ 1:N
        ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              complaint_documents                                             │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ complaint_id     INT (FK → complaints) ON DELETE CASCADE                                    │
│ file_name        VARCHAR(255) - Physical filename                                            │
│ file_path        VARCHAR(500) - Relative path                                               │
│ original_name    VARCHAR(255) - Original upload name                                        │
│ file_type        VARCHAR(50) - MIME type                                                    │
│ file_size        INT - Bytes                                                                 │
│ category         VARCHAR(50) - supporting/valid_id/handwritten_form                         │
│ upload_date      TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

        │
        │ 1:N
        ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                               complaint_history                                              │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ complaint_id     INT (FK → complaints) ON DELETE CASCADE                                    │
│ status           ENUM (same as complaints.status)                                           │
│ notes            TEXT                                                                        │
│ updated_by       VARCHAR(255)                                                                │
│ admin_user_id    INT (FK → admin_users)                                                     │
│ created_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

        │
        │ 1:N
        ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                             complaint_assignments                                            │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ complaint_id     INT (FK → complaints) ON DELETE CASCADE                                    │
│ assigned_to_unit VARCHAR(50) - OSDS/SGOD/CID                                                │
│ assigned_by      INT (FK → admin_users)                                                     │
│ notes            TEXT                                                                        │
│ created_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   admin_users                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ email            VARCHAR(255) UNIQUE                                                         │
│ password_hash    VARCHAR(255)                                                                │
│ full_name        VARCHAR(255)                                                                │
│ role_id          INT (FK → admin_roles)                                                     │
│ google_id        VARCHAR(255) - OAuth                                                        │
│ avatar_url       VARCHAR(500)                                                                │
│ unit             VARCHAR(50) - OSDS/SGOD/CID                                                │
│ is_active        TINYINT(1) DEFAULT 1                                                       │
│ last_login       TIMESTAMP                                                                   │
│ created_at       TIMESTAMP                                                                   │
│ updated_at       TIMESTAMP                                                                   │
│ created_by       INT (FK → admin_users)                                                     │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
        │
        │ N:1
        ▼
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                    admin_roles                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ name             VARCHAR(50) UNIQUE - Super Admin/Admin/Staff/Viewer                        │
│ description      VARCHAR(255)                                                                │
│ permissions      JSON                                                                        │
│ is_active        TINYINT(1) DEFAULT 1                                                       │
│ created_at       TIMESTAMP                                                                   │
│ updated_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                   activity_log                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ user_id          INT (FK → admin_users) ON DELETE SET NULL                                  │
│ action_type      ENUM('login','logout','view','create','update','delete','status_change',   │
│                       'forward','accept','return')                                          │
│ entity_type      VARCHAR(50)                                                                 │
│ entity_id        INT                                                                         │
│ description      TEXT                                                                        │
│ old_value        JSON                                                                        │
│ new_value        JSON                                                                        │
│ ip_address       VARCHAR(45)                                                                 │
│ user_agent       TEXT                                                                        │
│ created_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                     email_logs                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ recipient_email  VARCHAR(255)                                                                │
│ subject          VARCHAR(500)                                                                │
│ event_type       VARCHAR(100)                                                                │
│ reference_id     INT (FK → complaints)                                                      │
│ status           ENUM('sent','failed','skipped')                                            │
│ error_message    TEXT                                                                        │
│ created_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                              password_reset_tokens                                           │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ id (PK)          INT AUTO_INCREMENT                                                          │
│ user_id          INT (FK → admin_users) ON DELETE CASCADE                                   │
│ token            VARCHAR(255)                                                                │
│ expires_at       TIMESTAMP                                                                   │
│ used             TINYINT(1) DEFAULT 0                                                       │
│ created_at       TIMESTAMP                                                                   │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Directory Structure

```
CTS/
├── admin/                          # Admin Panel
│   ├── api/                        # REST API Endpoints
│   │   ├── analytics.php           # Analytics data API
│   │   ├── analytics-export.php    # Export analytics (CSV/Excel)
│   │   ├── complaint-action.php    # Accept/Return/Resolve actions
│   │   ├── delete-user.php         # Delete user API
│   │   ├── forward-complaint.php   # Forward to unit
│   │   ├── notification-count.php  # Real-time notification count
│   │   ├── save-user.php           # Create/Update user
│   │   ├── update-status.php       # Status change API
│   │   └── user-status.php         # Activate/Deactivate user
│   │
│   ├── assets/                     # Admin-specific assets
│   │   ├── css/admin.css           # Admin stylesheet
│   │   └── js/admin.js             # Admin JavaScript
│   │
│   ├── auth/                       # Authentication handlers
│   │   └── google-callback.php     # Google OAuth callback
│   │
│   ├── includes/                   # Shared admin components
│   │   ├── header.php              # Common header
│   │   ├── footer.php              # Common footer
│   │   └── sidebar.php             # Navigation sidebar
│   │
│   ├── index.php                   # Dashboard
│   ├── complaints.php              # Complaint list
│   ├── complaint-view.php          # Single complaint view
│   ├── users.php                   # User management
│   ├── analytics.php               # Analytics dashboard
│   ├── email-logs.php              # Email log viewer
│   ├── email-settings.php          # SMTP configuration
│   ├── logs.php                    # Activity log viewer
│   ├── profile.php                 # User profile
│   ├── login.php                   # Login page
│   └── logout.php                  # Logout handler
│
├── assets/                         # Public assets
│   ├── css/                        # Stylesheets
│   ├── js/                         # JavaScript files
│   ├── images/                     # Static images
│   └── uploads/                    # ★ Centralized uploads
│       ├── images/                 # Image uploads
│       └── documents/              # Document uploads
│
├── config/                         # Configuration files
│   ├── database.php                # PDO connection
│   ├── admin_config.php            # Admin settings
│   └── mail_config.php             # SMTP configuration
│
├── database/                       # Database schemas
│   ├── schema.sql                  # Main schema
│   ├── admin_schema.sql            # Admin tables
│   ├── email_logs_schema.sql       # Email logging
│   └── migrate_add_file_path.sql   # Migration script
│
├── models/                         # Data models
│   ├── Complaint.php               # Public complaint operations
│   ├── ComplaintAdmin.php          # Admin complaint operations
│   ├── AdminUser.php               # User authentication/management
│   ├── ActivityLog.php             # Activity logging
│   └── EmailLog.php                # Email log operations
│
├── services/                       # Business services
│   ├── ComplaintFormGenerator.php  # PDF form generation
│   └── email/                      # Email services
│       ├── EmailService.php        # PHPMailer wrapper
│       ├── ComplaintNotification.php # Notification handler
│       └── templates/              # Email templates
│
├── uploads/                        # Legacy upload location
│   ├── temp/                       # Temporary staging
│   └── complaints/                 # [DEPRECATED] Old structure
│
├── vendor/                         # Composer dependencies
│   └── phpmailer/                  # PHPMailer library
│
├── index.php                       # Complaint form
├── review.php                      # Review & submit
├── success.php                     # Confirmation page
├── track.php                       # Status tracking
├── contact.php                     # Contact page
│
├── .env                            # Environment variables
├── composer.json                   # Dependencies
└── README.md                       # Documentation
```

---

## Security Architecture

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                  SECURITY LAYERS                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ AUTHENTICATION                                                                               │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ ✅ Password hashing (bcrypt via password_hash/password_verify)                              │
│ ✅ Google OAuth 2.0 integration                                                             │
│ ✅ Session-based authentication                                                             │
│ ✅ Password reset token expiration                                                          │
│ ✅ Last login tracking                                                                       │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ AUTHORIZATION                                                                                │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ ✅ Role-based access control (RBAC)                                                         │
│    ├─ Super Admin: Full system access                                                       │
│    ├─ Admin: Complaints + Users + Reports                                                   │
│    ├─ Staff: Complaints only                                                                │
│    └─ Viewer: Read-only access                                                              │
│ ✅ Unit-based filtering (OSDS/SGOD/CID)                                                     │
│ ✅ Permission JSON structure for flexibility                                                │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ DATA PROTECTION                                                                              │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ ✅ PDO prepared statements (SQL injection prevention)                                       │
│ ✅ Input validation and sanitization                                                        │
│ ✅ CSRF protection (where implemented)                                                      │
│ ✅ Complaint lock after submission (is_locked flag)                                         │
│ ✅ Secure file naming (no user input in filenames)                                          │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ FILE UPLOAD SECURITY                                                                         │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ ✅ Allowed extensions: PDF, JPG, JPEG, PNG only                                             │
│ ✅ File size limits enforced                                                                 │
│ ✅ MIME type checking                                                                        │
│ ✅ Unique filenames with timestamps                                                         │
│ ✅ Separated storage (images/ vs documents/)                                                │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│ AUDIT & ACCOUNTABILITY                                                                       │
├─────────────────────────────────────────────────────────────────────────────────────────────┤
│ ✅ Comprehensive activity logging                                                            │
│ ✅ IP address and user agent tracking                                                       │
│ ✅ Old value/new value change tracking                                                      │
│ ✅ Email notification logging                                                                │
│ ✅ Complaint status history with timestamps                                                 │
└─────────────────────────────────────────────────────────────────────────────────────────────┘
```

---

## Technology Stack

```
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                  TECHNOLOGY STACK                                            │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌────────────────────┬────────────────────────────────────────────────────────────────────────┐
│ Backend            │ PHP 7.4+ / 8.x                                                         │
│ Database           │ MySQL 5.7+ / MariaDB                                                   │
│ Web Server         │ Apache (XAMPP)                                                         │
│ Email              │ PHPMailer with SMTP                                                    │
│ Package Manager    │ Composer                                                               │
├────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ Frontend           │ HTML5, CSS3, JavaScript (ES6+)                                         │
│ UI Framework       │ Bootstrap 5                                                            │
│ Icons              │ Bootstrap Icons / Font Awesome                                         │
│ Charts             │ Chart.js (Analytics)                                                   │
├────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ Authentication     │ Native PHP Sessions                                                    │
│ OAuth              │ Google OAuth 2.0                                                       │
│ Password           │ bcrypt (password_hash)                                                 │
├────────────────────┼────────────────────────────────────────────────────────────────────────┤
│ Environment        │ .env configuration                                                     │
│ Database Access    │ PDO (PHP Data Objects)                                                 │
│ Character Set      │ UTF-8 (utf8mb4_unicode_ci)                                             │
└────────────────────┴────────────────────────────────────────────────────────────────────────┘
```

---

**Architecture Version:** 1.0  
**Last Updated:** January 29, 2026  
**System:** SDO CTS - San Pedro Division Office Complaint Tracking System
