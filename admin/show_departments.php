<?php
include '../includes/admin-init.php';
include '../includes/admin-header.php';
include '../includes/admin-nav.php';
?>
</head>

<body>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="<?php echo BASE_URL; ?>assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Department Details Panel
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="dept-center row ">
        <!-- Nav Tabs -->
       <div class="d-flex justify-content-between align-items-center mb-4">
    </div>

    <!-- Display session messages -->
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?= $_SESSION['success_message'] ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error_message'] ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <!-- Filters and Download -->
    <div class="row mb-4 align-items-end">
        <div class="col-md-3">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" id="category">
                <option value="">All</option>
                <?php
                // Fetch active categories from database
                $categoryStmt = $conn->prepare("SELECT name FROM dept_category WHERE status = 1 ORDER BY name ASC");
                $categoryStmt->execute();
                $categoryResult = $categoryStmt->get_result();
                
                while ($category = $categoryResult->fetch_assoc()) {
                    echo '<option value="' . htmlspecialchars($category['name']) . '">' . htmlspecialchars($category['name']) . '</option>';
                }
                $categoryStmt->close();
                ?>
            </select>
        </div>

        <div class="col-md-3">
            <label for="area" class="form-label">Area</label>
            <input type="text" class="form-control" id="area" placeholder="Enter Area (e.g., durga market)">
        </div>

        <!-- Buttons Container -->
        <div class="col-md-6 d-flex align-items-end">
            <div>
                <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#downloadModal">
                 <i class="fa fa-download"></i>   
                </a>
            </div>
            <div class="ms-2">
                <label class="form-label d-block invisible">Add</label> <!-- Spacer label -->
                <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#departmentModal">
                    <i class="fas fa-building"></i> Add Department
                </button>
            </div>
        </div>
    </div>

    <!-- Add this at the top of your main content area -->
    <ul class="nav nav-tabs mb-2" id="deptTab" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="dept-list-tab" data-bs-toggle="tab" href="#dept-list" role="tab" aria-controls="dept-list" aria-selected="true">Department List</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="forwarded-tab" data-bs-toggle="tab" href="#forwarded" role="tab" aria-controls="forwarded" aria-selected="false">Forwarded Complaints</a>
      </li>
    </ul>
        <!-- Complaints Table -->
        <div class="table-responsive bg-light rounded shadow-sm p-3">
          <div id="deptTableContainer">
        <table id="departmentTable" class="table table-hover table-borderless">
            <thead class="table-dark">
                <tr>
                    <th>Sr No.</th>
                    <th>Category</th>
                    <th>Area</th>
                    <th>Name</th> 
                    <th>Contact</th>
                    <th>Gmail</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            <?php 

            $stmt=$conn->prepare("SELECT * FROM departments");
            $stmt->execute();
            $result=$stmt->get_result();
            $id=1;
    
while($row = $result->fetch_assoc()){
    echo "
    <tr>
        <td>" . $id++ . "</td>
        <td>" . htmlspecialchars($row['category']) . "</td>
        <td>" . htmlspecialchars($row['area']) . "</td>
        <td>" . htmlspecialchars($row['name']) . "</td>
        <td>" . htmlspecialchars($row['phone']) . "</td>
        <td>" . htmlspecialchars($row['email']) . "</td>
        <td>
          <button 
            class='btn btn-warning border btn-sm edit-btn' 
            data-bs-toggle='modal' 
            data-bs-target='#editDepartment'
            data-id='" . htmlspecialchars($row['id']) . "'
            data-category='" . htmlspecialchars($row['category']) . "'
            data-area='" . htmlspecialchars($row['area']) . "'
            data-name='" . htmlspecialchars($row['name']) . "'
            data-phone='" . htmlspecialchars($row['phone']) . "'
            data-email='" . htmlspecialchars($row['email']) . "'
          >
              <i class='fa fa-pencil' aria-hidden='true'></i>
          </button>

          <button 
            class='btn btn-danger border btn-sm delete-btn' 
            data-id='" . htmlspecialchars($row['id']) . "'
            data-csrf='" . csrf_token() . "'
          >
              <i class='fa fa-trash' aria-hidden='true'></i>
          </button>

          <button class='btn btn-success border btn-sm w-80'>
              <a href='../admin/reports/excel.php?type=departments&filter=" . urlencode($row['id']) . "' class='text-white text-decoration-none'>
                <i class='fa fa-download'></i>
              </a>
          </button>
        </td>
    </tr>
    ";
}

?>
            </tbody>
        </table>
        </div>
        <div id="forwardComplaintContainer" class="d-none">
        <table id="forwardedComplaintsTable" class="table table-hover table-borderless" style="width:100%">
            <thead class="table-dark">
                <tr style="border-radius:20px;">
                    <th>Sr No.</th>
                    <th>Ref ID</th>
                    <th>Dept Name</th>
                    <th>Dept Category</th>
                    <th>Complaint</th>
                    <!-- <th>User Name</th> -->
                    <th>User Location</th>
                    <th>Forwarded At</th>
                    <th>Priority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT  
                                cf.complaint_ref_id,
                                cf.dept_name,
                                cf.dept_category,
                                cf.complaint,
                                cf.user_location,
                                cf.forwarded_at,
                                cf.priority,
                                cf.status
                            FROM 
                                complaint_forwarded cf
                            ORDER BY 
                                cf.forwarded_at DESC";

                    $stmt = $conn->prepare($sql);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    $count = 1;
                    while ($row = $result->fetch_assoc()) {
                        $status = htmlspecialchars($row['status']);
                        $statusClass = '';
                        $priority = htmlspecialchars($row['priority']);
                        $priorityClass = '';

                        switch (strtolower($status)) {
                            case 'forwarded':
                                $statusClass = 'badge bg-warning text-dark';
                                break;
                            case 'resolve':
                                $statusClass = 'badge bg-success';
                                break;
                            default:
                                $statusClass = 'badge bg-secondary';
                        }

                        // Priority badge color (match complaints.php)
                        switch (strtolower($priority)) {
                            case 'low':
                                $priorityClass = 'priority-badge bg-low';
                                break;
                            case 'medium':
                                $priorityClass = 'priority-badge bg-medium';
                                break;
                            case 'high':
                                $priorityClass = 'priority-badge bg-high';
                                break;
                            case 'urgent':
                                $priorityClass = 'priority-badge bg-urgent';
                                break;
                            default:
                                $priorityClass = 'priority-badge bg-secondary';
                        }

                        echo "<tr>
                                <td>" . $count++ . "</td>
                                <td>" . htmlspecialchars($row['complaint_ref_id']) . "</td>
                                <td>" . htmlspecialchars($row['dept_name']) . "</td>
                                <td>" . htmlspecialchars($row['dept_category']) . "</td>
                                <td>" . htmlspecialchars($row['complaint']) . "</td>
                                <td>" . htmlspecialchars($row['user_location']) . "</td>
                                <td>" . htmlspecialchars($row['forwarded_at']) . "</td>
                                <td><span class='" . $priorityClass . "'>" . ucfirst($priority) . "</span></td>
                                <td><span class='" . $statusClass . "'>" . ucfirst($status) . "</span></td>
                              </tr>";
                    }

                    $stmt->close();
                } catch (Exception $e) {
                    echo "<tr><td colspan='9' class='text-danger'>Error loading data</td></tr>";
                    error_log($e->getMessage());
                }
                ?>
            </tbody>
        </table>
    </div>
    </div>
  

  </div>

</div>
 

<div class="modal fade" id="editDepartment" tabindex="-1" aria-labelledby="EditdepartmentDetailsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="departmentDetailsLabel">Edit Department Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" id="saveEditDetails">
        <?= csrf_field() ?>
        <div class="modal-body">

          <div class="mb-3">
            <label for="deptCategory" class="form-label">Category</label>
            <select class="form-select" id="edit_category" name="category">
              <option value="">-- Select Category --</option>
              <?php
              // Fetch active categories from database
              $editCategoryStmt = $conn->prepare("SELECT name FROM dept_category WHERE status = 1 ORDER BY name ASC");
              $editCategoryStmt->execute();
              $editCategoryResult = $editCategoryStmt->get_result();
              
              while ($editCategory = $editCategoryResult->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($editCategory['name']) . '">' . htmlspecialchars($editCategory['name']) . '</option>';
              }
              $editCategoryStmt->close();
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="deptName" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="edit_name" name="name" required>
          </div>

          <div class="mb-3">
            <label for="deptEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="edit_email" name="email" required>
          </div>

          <div class="mb-3">
            <label for="deptPhone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="edit_phone" name="phone" required pattern="[0-9]{10}" maxlength="10">
          </div>

          <div class="mb-3">
            <label for="deptArea" class="form-label">Area</label>
            <textarea class="form-control" id="edit_area" name="area" required>
            </textarea>
          </div>

        </div>
        <div class="modal-footer">
            <input type="hidden" name="id" id="edit_id" >
             <button type="button" id="" data-bs-dismiss="modal" class="btn btn-danger text-white">Close</button>
          <button type="button" id="updateDept" class="btn btn-warning text-white">Update Department</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Create Department Modal -->
<div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="createDeptLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="createDeptLabel">Create Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="../admin/create-department.php" method="POST">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label for="departmentCategory" class="form-label">Select Category</label>
            <select class="form-select" id="departmentCategory" name="category" required>
              <option value="">-- Choose Category --</option>
              <?php
              // Fetch active categories from database
              $addCategoryStmt = $conn->prepare("SELECT name FROM dept_category WHERE status = 1 ORDER BY name ASC");
              $addCategoryStmt->execute();
              $addCategoryResult = $addCategoryStmt->get_result();
              
              while ($addCategory = $addCategoryResult->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($addCategory['name']) . '">' . htmlspecialchars($addCategory['name']) . '</option>';
              }
              $addCategoryStmt->close();
              ?>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">Next</button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- Department Details Modal -->
<div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="departmentDetailsLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-warning text-white">
        <h5 class="modal-title" id="departmentDetailsLabel">Add Department Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="" method="POST" id="saveDepartmentDetails">
        <?= csrf_field() ?>
        <div class="modal-body">

          <div class="mb-3">
            <label for="deptCategory" class="form-label">Category</label>
            <select class="form-select" id="deptCategory" name="category">
              <option value="">-- Select Category --</option>
              <?php
              // Fetch active categories from database
              $deptCategoryStmt = $conn->prepare("SELECT name FROM dept_category WHERE status = 1 ORDER BY name ASC");
              $deptCategoryStmt->execute();
              $deptCategoryResult = $deptCategoryStmt->get_result();
              
              while ($deptCategory = $deptCategoryResult->fetch_assoc()) {
                  echo '<option value="' . htmlspecialchars($deptCategory['name']) . '">' . htmlspecialchars($deptCategory['name']) . '</option>';
              }
              $deptCategoryStmt->close();
              ?>
            </select>
          </div>

          <div class="mb-3">
            <label for="deptName" class="form-label">Department Name</label>
            <input type="text" class="form-control" id="deptName" name="name" required>
          </div>

          <div class="mb-3">
            <label for="deptEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="deptEmail" name="email" required>
          </div>

          <div class="mb-3">
            <label for="deptPhone" class="form-label">Phone</label>
            <input type="tel" class="form-control" id="deptPhone" name="phone" required pattern="[0-9]{10}" maxlength="10">
          </div>

          <div class="mb-3">
            <label for="deptArea" class="form-label">Area</label>
            <textarea class="form-control" id="deptArea" name="area" required>
            </textarea>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" id="saveDept" class="btn btn-warning text-white">Save Department</button>
        </div>
      </form>
    </div>
  </div>
</div>
   <!-- Download Modal -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <form action="../admin/reports/excel.php" method="POST" id="downloadExcelForm">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="downloadModalLabel">
                        <i class="fas fa-download me-2"></i>Download Complaints Data
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="complaintStatus" class="form-label fw-semibold">Select Complaint Type</label>
                        <select class="form-select" id="complaintStatus" name="complaint_status" required>
                            <option value="departments">Departments List</option>
                            <option value="forwarded_complaints">Forwarded Complaints</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-download me-1"></i>Download
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize DataTables

    $('#forwardedComplaintsTable').DataTable({
    });

    // Tab toggling logic
    const deptTab = document.querySelector('#dept-list-tab');
    const forwardedTab = document.querySelector('#forwarded-tab');
    const deptContainer = document.querySelector('#deptTableContainer');
    const forwardContainer = document.querySelector('#forwardComplaintContainer');

    deptTab.addEventListener('click', () => {
        deptContainer.classList.remove('d-none');
        forwardContainer.classList.add('d-none');
    });

    forwardedTab.addEventListener('click', () => {
        forwardContainer.classList.remove('d-none');
        deptContainer.classList.add('d-none');
    });

    // Optional: Activate correct container on reload
    const activeTab = document.querySelector('.nav-link.active');
    if (activeTab && activeTab.id === 'forwarded-tab') {
        forwardContainer.classList.remove('d-none');
        deptContainer.classList.add('d-none');
    }

    // Set the type input based on dropdown selection
    const complaintStatus = document.getElementById('complaintStatus');
    const downloadType = document.getElementById('downloadType');
    const downloadForm = document.getElementById('downloadExcelForm');
    if (complaintStatus && downloadType && downloadForm) {
        complaintStatus.addEventListener('change', function() {
            downloadType.value = this.value;
        });
        // Set type before submit in case user doesn't change dropdown
        downloadForm.addEventListener('submit', function() {
            downloadType.value = complaintStatus.value;
        });
    }
});
</script>

<style>
    .priority-badge {
        color: #fff !important;
        border-radius: 0.25rem;
        padding: 0.35em 0.65em;
        font-size: .75em;
        font-weight: 700;
        line-height: 1;
        text-align: center;
        white-space: nowrap;
        vertical-align: baseline;
        border: none;
    }
    .bg-low { background-color: #0dcaf0 !important; } /* Bootstrap info */
    .bg-medium { background-color: #0d6efd !important; } /* Bootstrap primary */
    .bg-high { background-color: #ffc107 !important; color: #000 !important; } /* Bootstrap warning */
    .bg-urgent { background-color: #dc3545 !important; } /* Bootstrap danger */
</style>

    <?php
    include '../includes/admin-footer.php';
    ?>

</body>
</html>

