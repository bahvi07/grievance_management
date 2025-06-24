<?php 
// Start session first before any CSRF operations
require_once '../config/session-config.php';
startSecureSession();

// Include config for CSRF functions
require_once '../config/config.php';

// Use include_once to prevent multiple inclusions and remove the redundant config include
include_once '../includes/header.php';
include_once '../auth/auth-check.php';
?>
</head>

<body>

  <!-- Top Header Bar -->
  <div class="top-header">
    <div class="brand">
      <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo">
      Vidhayak Seva Kendra. 
    </div>
    <button class="sidebar-toggle" id="toggleSidebar">☰</button>
  </div>

  <!-- Sidebar -->
  <div class="sidebar" id="sidebar">
    <ul class="nav flex-column">
      <li><a class="nav-link" href="dashboard.php"><i class="fas fa-home"></i> Home</a></li>
      <li><a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#complaintModal"><i class="fas fa-plus-circle"></i> Create Complaint</a></li>
      <li><a class="nav-link" href="./myComplaints.php"><i class="fas fa-list-alt"></i> My Complaints</a></li>
      <li><a class="nav-link" href="#" data-bs-target="#checkStatus" data-bs-toggle="modal"><i class="fas fa-tasks"></i> Check Status</a></li>
      <li><a class="nav-link" href="#" data-bs-target="#edit-profile" data-bs-toggle="modal"><i class="fas fa-user-edit"></i> Edit Phone</a></li>
      <li><a href="#" class="nav-link" data-bs-target="#feedback" data-bs-toggle="modal"> <i class="fa-solid fa-comments"></i>
Feedback</a></li>
      <li><a class="nav-link" href="#"  data-bs-toggle="modal" data-bs-target="#deleteAcModal"><i class="fas fa-trash-alt"></i> Delete Account</a></li>
      <li><a class="nav-link" href="../auth/logout.php"><i class="fas fa-sign-out-alt"></i> Log Out</a></li>
    </ul>
  </div>

  <!-- Main Content -->
  <div class="content ">
    <div class="row gx-3 gy-3">
      <div class="col-12">
        <div class="dashboard-card" id="create" data-bs-toggle="modal" data-bs-target="#complaintModal">
          <i class="fas fa-plus-circle"></i>
          <span>Create Complaint</span>
        </div>
      </div>

      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="show">
          <i class="fas fa-list-alt"></i>
          <span>My Complaints</span>
        </div>
      </div>

      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="status" data-bs-toggle="modal" data-bs-target="#checkStatus">
          <i class="fas fa-tasks"></i>
          <span>Check Status</span>
        </div>
      </div>


      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="user-profile" data-bs-toggle="modal" data-bs-target="#edit-profile">
          <i class="fas fa-user-edit"></i>
          <span>Edit Phone</span>
        </div>
      </div>

      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="office">
          <i class="fas fa-building"></i>
          <span>Our Office</span>
        </div>
      </div>

      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="anonymus-comp" data-bs-target="#feedback" data-bs-toggle="modal">
      <i class="fa-solid fa-comments"></i>
          <span>Feedback</span>
        </div>
      </div>
      <div class="col-6 col-md-6">
        <div class="dashboard-card" id="logout">
          <i class="fas fa-reply"></i>
          <span>Logout</span>
        </div>
      </div>

      <!-- Change this ID from "delete" to "openDeleteModal" -->
      <div class="col-12 ">
        <div class="dashboard-card" id="openDeleteModal" data-bs-toggle="modal" data-bs-target="#deleteAcModal">
          <i class="fas fa-trash-alt"></i>
          <span>Delete Account</span>
        </div>
      </div>

    </div>
  </div>


  <!-- Complaint Form Modal -->
  <div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="complaintModalLabel">Enter Complaint Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form method="post" enctype="multipart/form-data" id="complaintForm">
            <?= csrf_field() ?>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="name" class="form-label">Name:</label>
                <input type="text" class="form-control" id="name" name="name" required>
              </div>
              <div class="col-md-6">
                <label for="fatherName" class="form-label">Father's Name:</label>
                <input type="text" class="form-control" id="fatherName" name="fName" required>
              </div>
            </div>

            <div class="mb-3">
              <label for="email" class="form-label">Email:</label>
              <input type="email" class="form-control" id="email" name="email" required>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Location:</label>
                <textarea class="form-control location-display" id="locationDisplay" name="location" rows="2" placeholder="Enter Location"></textarea>
              </div>
              <div class="col-md-6">
                <label for="img" class="form-label">Upload Photo:</label>
                <input type="file" class="form-control" id="img" name="img" accept="image/*">
              </div>
            </div>

            <div class="mb-3 ">
              <label for="complain" class="form-label">Complaint Category:</label>
              <select class="form-select text-center" id="complain" name="category" required>
                <option value="">-- Select Option --</option>
                <option value="Water">Water Related</option>
                <option value="Electricity">Electricity Related</option>
                <option value="Land">Land Related</option>
                  <option value="Sanitation">Sanitation</option>
                  <option value="Road">Road</option>
                <option value="Other">Other</option>
              </select>
            </div>

            <div class="mb-3">
              <label for="complaint" class="form-label">Describe Your Complaint:</label>
              <textarea class="form-control" id="complaint" name="complaint" rows="3" maxlength="300" required></textarea>
            </div>

            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
              <button type="button" id="submitComplain" class="btn btn-primary">Submit</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Check Status Modal -->
  <div class="modal fade" id="checkStatus" tabindex="-1" aria-labelledby="checkStatusLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4 p-0">
        <div class="modal-header rounded-top-4" style="background:#F15922;">
          <h5 class="modal-title text-white" id="checkStatusLabel">
            <i class="fas fa-search me-2"></i>Check Complaint Status
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form action="./viewStatus.php" id="viewStatus" method="post">
          <?= csrf_field() ?>
          <div class="modal-body">
            <div class="mb-3">
              <label for="refid" class="form-label">Reference ID:</label>
              <input type="text" class="form-control" id="refid" name="refid" required>
            </div>
          </div>

          <div class="modal-footer">
            <button type="submit" data-bs-target="#checkStatus" data-bs-toggle="modal" class="btn w-100 text-white" id="chk" style="background:#F15922;">Check Status</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Delete Account Modal -->
  <div class="modal fade" id="deleteAcModal" tabindex="-1" aria-labelledby="deleteAcModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content rounded-4">

        <!-- Modal Header -->
        <div class="modal-header rounded-top-4" style="">
          <h5 class="modal-title text-dark" id="deleteAcModalLabel">
            <i class="fas fa-user-times me-2"></i>Delete Account
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <!-- Modal Form -->
        <form action="" method="POST" id="deleteForm">
          <?= csrf_field() ?>
          <div class="modal-body">
            <div class="text-center">
              <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
              <h4 class="text-danger mt-3">⚠️ WARNING</h4>
              <p class="text-danger fw-semibold">This action cannot be undone!</p>
              <p>Are you sure you want to permanently delete your account and all your complaints?</p>
            </div>
            <input type="hidden" name="phone" value="<?= $_SESSION['user_phone'] ?? '' ?>">
          </div>

          <!-- Modal Footer with Buttons -->
          <!-- Inside the modal footer -->
          <div class="modal-footer d-flex flex-column gap-2">
            <button type="button" id="delete" class="btn btn-danger w-100 rounded-pill">
              <i class="fas fa-trash-alt me-2"></i>Yes, Delete My Account
            </button>
            <button type="button" class="btn btn-secondary w-100 rounded-pill" data-bs-dismiss="modal">
              Cancel
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>


  <!-- Edit profile Modal -->
  <div class="modal fade" tabindex="-1" id="edit-profile">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Phone</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form action="" method="POST" id="editPhoneForm">
            <?= csrf_field() ?>
            <div class="mb-3">
              <label for="newPhone" class="form-label">New Phone Number:</label>
              <input type="tel" class="form-control" id="newPhone" name="newPhone" pattern="^[6-9]\d{9}$" maxlength="10" required>
              <small class="text-muted">Enter a valid 10-digit phone number starting with 6-9</small>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="button" class="btn btn-primary" id="updatePhone">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

<!-- Feedback Modal -->
<div class="modal fade" id="feedback" tabindex="-1" aria-labelledby="feedbackLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="feedbackForm" method="POST" action="">
        <?= csrf_field() ?>
        <div class="modal-header" style="background:#F15922;">
          <h5 class="modal-title text-white" id="feedbackLabel">
            <i class="fa-solid fa-comments me-2"></i>Feedback
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="name" class="form-label">Your Name</label>
            <input type="text" class="form-control" name="feed_u_name" require>
            <label for="feedbackText" class="form-label">Your Feedback</label>
            <textarea class="form-control" id="feedbackText" name="feedback" rows="4" maxlength="500" required></textarea>
          </div>
          <!-- Hidden fields for user details -->
          <input type="hidden" name="user_phone" value="<?= $_SESSION['user_phone']; ?>">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" id="submit_feedback">Submit Feedback</button>
        </div>
      </form>
    </div>
  </div>
</div>

  <?php include '../includes/footer.php'; ?>

  <script>
    document.getElementById('show').addEventListener('click', () => {
      window.location.href = "./myComplaints.php";
    });


    document.getElementById('logout').addEventListener('click', () => {
      window.location.href = "../auth/logout.php";
    });

    // Delete Account Script
    const delBtn = document.getElementById('delete');

    delBtn.addEventListener('click', async (e) => {
      e.preventDefault();

      const form = document.getElementById("deleteForm");
      const formData = new FormData(form);

      // Disable button while processing
      delBtn.disabled = true;
      delBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Deleting...`;

      try {
        const response = await fetch('./delete-account.php', {
          method: 'POST',
          body: formData,
        });

        const text = await response.text();
        const result = JSON.parse(text);

        if (result.success) {
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('deleteAcModal'));
          modal.hide();

          // Clear any client-side storage
          localStorage.clear();
          sessionStorage.clear();

          // Clear any cookies that might be set by JavaScript
          document.cookie.split(";").forEach(function(c) { 
            document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"); 
          });

          // Show success message and redirect
          Swal.fire({
            icon: 'success',
            title: 'Account Deleted',
            text: result.message,
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            // Force redirect to login page
            window.location.replace('../auth/login.php');
          });

        } else {
          // Show error if deletion fails
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: result.message || 'Unable to delete account'
          });
        }

      } catch (error) {
        console.error("Error:", error);
        Swal.fire({
          icon: 'error',
          title: 'Network Error',
          text: 'Please try again.'
        });
      } finally {
        delBtn.disabled = false;
        delBtn.innerHTML = '<i class="fas fa-trash-alt me-2"></i>Yes, Delete My Account';
      }
    });
  </script>