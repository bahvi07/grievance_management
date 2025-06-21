## Complaint Management System: Project Flow

Here is an enhanced and detailed project flow, tailored for a presentation, based on the application's codebase.

---

### 👤 User-Facing Application Flow

**1. Secure User Authentication (Mobile OTP)**
- **Initiation**: The user enters their 10-digit mobile number on the login screen.
- **OTP Delivery**: The system generates a secure 6-digit One-Time Password (OTP) and sends it to the user.
- **Verification**: The user submits the received OTP.
- **Access Granted**: Upon correct OTP validation, the user is logged in and redirected to their personal dashboard.
- **Security Protocol**: To prevent abuse, the system implements a strict rate-limiting policy. After 5 incorrect OTP attempts, the user's account (linked to the phone number and IP address) is **locked for 10 minutes**.

**2. User Dashboard**
- A central, responsive hub for all user activities. It features a sidebar menu on desktop and a mobile-friendly toggle navigation.

**3. Submitting a New Complaint**
- **Data Collection**: Users fill out a comprehensive form with the following fields:
    - Full Name (Required)
    - Father's Name (Required)
    - Email Address (Required)
    - Contact Number (Auto-filled from login)
    - Complaint Category (e.g., Water Supply, Electricity, Public Works)
    - Detailed Complaint Description
    - Location/Area
    - Optional Image Upload (supports JPG, PNG, GIF up to 2MB)
- **Confirmation**: Upon submission, a unique **6-digit Reference ID** is generated and instantly displayed to the user for future tracking.

**4. My Complaints Overview**
- Users can view a detailed table of all their submitted complaints.
- The table provides at-a-glance information with columns for:
    - Reference ID
    - Name
    - Location
    - Current Status (e.g., New, Forwarded, Resolved, Rejected)
    - Department Response/Notes (once available)

**5. Track Complaint Status**
- A dedicated feature allows users to check the status of a specific complaint by entering its **Reference ID**, providing a quick and direct way to get updates without logging in.

**6. Profile Management**
- **Edit Phone Number**: Users have the ability to securely update their registered mobile number.
- **Delete Account**: A user can choose to permanently delete their account. This action requires confirmation and results in the complete removal of all their personal data and associated complaints from the system.

**7. Additional Features**
- **Our Office**: Provides static information about department locations and contact details.
- **Feedback System**: A simple form for users to submit feedback about the platform or their experience.
- **Logout**: Securely terminates the user's session.

---

### 🛠️ Administrative Backend Flow

**1. Secure Admin Login**
- **Credentials**: Admins log in using a unique Admin ID and a password.
- **Lockout Mechanism**: Similar to the user flow, 5 consecutive failed login attempts will trigger a temporary **account lockout for 5-10 minutes** to protect against brute-force attacks.

**2. Central Admin Dashboard**
- The command center for administrators, providing key metrics and at-a-glance insights.
- **Quick Statistics**: Real-time counts of complaints:
    - New, Pending, Forwarded, Resolved, Rejected.
- **Data Visualizations & Charts**:
    - **Monthly Performance**: A bar or line chart illustrating the number of complaints resolved each month.
    - **Problem Hotspots**: A chart identifying the most complaint-prone areas, categorized by issue type.
    - **Status Overview**: A pie chart showing the current ratio of Resolved vs. Pending vs. Rejected complaints.
- **Recent Activity**: A table displaying the most recently resolved complaints (e.g., from the last 2-5 days).

**3. Comprehensive Complaint Management**
- **New Complaints Queue**:
    - Admins review new submissions and can either **Forward** them to the relevant department or **Reject** them with a justification.
    - Forwarding involves searching for the correct department by area/category and triggers an email notification containing the full complaint details.
- **Rejected Complaints Bin**:
    - Provides an option to reconsider a rejected complaint and "Resend" it into the workflow if a mistake was made.
- **Forwarded & In-Progress**:
    - When a department provides a resolution update, the admin can mark the complaint as **"Resolved"** and add a final resolution note, which becomes visible to the user.

**4. Department & Data Management**
- **Department CRUD**: Admins can easily Add, Edit, and Delete department information.
- **Data Export**: The entire list of departments can be downloaded as an **Excel spreadsheet**.

**5. User Feedback Review**
- A dedicated section where administrators can view and analyze all feedback submitted by users.

**6. Admin Settings & Security**
- **Profile Updates**: Admins can update their own profile information (Name, Email, Phone).
- **Password Reset**: A secure password reset flow that sends a verification code to the admin's registered email address.
- **Logout**: Securely ends the admin session. 