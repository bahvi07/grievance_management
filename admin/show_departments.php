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
    <div class="complaint-center row ">
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
                <option>Electricity</option>
                <option>Water</option>
                <option>Road</option>
                <option>Land</option>
                  <option>Sanitation</option>
            </select>
        </div>

        <div class="col-md-3">
            <label for="area" class="form-label">Area</label>
            <input type="text" class="form-control" id="area" placeholder="Enter Area (e.g., durga market)">
        </div>

        <!-- Buttons Container -->
        <div class="col-md-6 d-flex align-items-end">
            <div>
                <a href="../admin/reports/excel.php?type=departments&filter=all" class="btn btn-success">
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
    <ul class="nav nav-tabs" id="deptTab" role="tablist">
      <li class="nav-item" role="presentation">
        <a class="nav-link active" id="dept-list-tab" data-bs-toggle="tab" href="#dept-list" role="tab" aria-controls="dept-list" aria-selected="true">Department List</a>
      </li>
      <li class="nav-item" role="presentation">
        <a class="nav-link" id="forwarded-tab" data-bs-toggle="tab" href="#forwarded" role="tab" aria-controls="forwarded" aria-selected="false">Forwarded Complaints</a>
      </li>
    </ul>
    <div class="tab-content" id="deptTabContent">
      <div class="tab-pane fade show active" id="dept-list" role="tabpanel">
        <!-- Complaints Table -->
        <div class="table-responsive bg-light rounded shadow-sm p-3">
        <table id="departmentTable" class="table table-hover table-borderless">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
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
  </div>
  <div class="tab-pane fade" id="forwarded" role="tabpanel">
    <div class="table-responsive">
        <table id="forwardedComplaintsTable" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Ref ID</th>
                    <th>Dept Name</th>
                    <th>Dept Category</th>
                    <th>Complaint</th>
                    <th>User Name</th>
                    <th>User Location</th>
                    <th>Forwarded At</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- Data will be loaded here by DataTables -->
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
              <option value="Electricity">Electricity</option>
              <option value="Water">Water</option>
              <option value="Road">Road</option>
              <option value="Land">Land</option>
              <option value="Sanitation">Sanitation</option>
              <option value="Other">Other</option>
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
              <option value="Electricity">Electricity</option>
              <option value="Water">Water</option>
              <option value="Road">Road</option>
              <option value="Land">Land</option>
              <option value="Sanitation">Sanitation</option>
              <option value="Other">Other</option>
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
              <option value="Electricity">Electricity</option>
              <option value="Water">Water</option>
              <option value="Road">Road</option>
              <option value="Land">Land</option>
              <option value="Sanitation">Sanitation</option>
              <option value="Other">Other</option>
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
    <?php
    include '../includes/admin-footer.php';
    ?>

<script>
$(document).ready(function() {
    // Initialize existing DataTable for departments
    $('#departmentsTable').DataTable();

    // Initialize new DataTable for forwarded complaints
    var forwardedTable = $('#forwardedComplaintsTable').DataTable({
        "ajax": {
            "url": "./data/list_forwarded.php",
            "type": "POST", // Using POST as it's often better for APIs
            "dataSrc": "data"
        },
        "columns": [
            { "data": "id" },
            { "data": "complaint_ref_id" },
            { "data": "dept_name" },
            { "data": "dept_category" },
            { "data": "complaint" },
            { "data": "user_name" },
            { "data": "user_location" },
            { "data": "forwarded_at" },
            { "data": "status" }
        ],
        "responsive": true,
        "scrollX": true,
        "autoWidth": false
    });

    // Handle tab switching to redraw DataTable
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        // Check if the new tab is the one with our table
        if (e.target.hash == '#forwarded') {
            // Redraw the table to recalculate column widths
            forwardedTable.columns.adjust().responsive.recalc();
        }
    });
});
</script>
</body>
</html>

