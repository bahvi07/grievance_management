<?php
include '../includes/admin-header.php';
include '../config/config.php';
include '../includes/admin-nav.php';
include '../auth/admin-auth-check.php';
?>
</head>

<body>
    <div class="top-head bg-light">
        <div class="brand">
            <img src="../assets/images/general_images/Bjplogo.jpg" alt="Logo">
            Department Details Panel
        </div>
        <div class="">
            Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
        </div>
    </div>
    <div class="complaint-center row ">
        <!-- Nav Tabs -->
       <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-orange">Show Department</h3>
            <p class="text-muted">View and search all department lists from here</p>
        </div>
        <button class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#departmentModal">
            <i class="fas fa-building"></i> Add Department
        </button>
    </div>

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

        <!-- Download Button -->
        <div class="col-md-3">
            <label class="form-label d-block">Download</label>
            <a href="../admin/reports/excel.php?type=departments&filter=all" class="btn btn-success w-50">
                <i class="fa fa-download"></i>
            </a>
        </div>
    </div>
        <!-- Complaints Table -->
        <div class="table-responsive bg-light rounded shadow-sm p-3">
        <table id="departmentTable" class="table table-bordered table-hover">
            <thead class="table-light">
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

 
<form method='post' class='delete-form' style='display:inline'>
    <input type='hidden' name='delete' value='" . htmlspecialchars($row['name']) . "'>
    <button class='btn btn-danger border btn-sm' type='submit'>
        <i class='fa fa-trash' aria-hidden='true'></i>
    </button>
</form>
<button class='btn btn-success border btn-sm w-80'>
    <a href='../admin/reports/excel.php?type=departments&filter=" . urlencode($row['id']) . "' class='text-white text-decoration-none'>
      <i class='fa fa-download'></i>
    </a>
</button>

<form method='POST' action='../mail_api/forward-mail.php' class='d-inline forwardForm'>
  <input type='hidden' name='refid' value='" . htmlspecialchars($refid) . "'>
  <input type='hidden' name='dept_email' value='" . htmlspecialchars($row['email']) . "'>
  <input type='hidden' name='name' value='" . htmlspecialchars($complaintData['name'] ?? '') . "'>
  <input type='hidden' name='email' value='" . htmlspecialchars($complaintData['email'] ?? '') . "'>
  <input type='hidden' name='phone' value='" . htmlspecialchars($complaintData['phone'] ?? '') . "'>
  <input type='hidden' name='location' value='" . htmlspecialchars($complaintData['location'] ?? '') . "'>
  <input type='hidden' name='description' value='" . htmlspecialchars($complaintData['complaint'] ?? '') . "'>
  <input type='hidden' name='image' value='" . htmlspecialchars(basename($complaintData['image'] ?? '')) . "'>
  <button type='submit' class='btn btn-sm btn-success mt-2'>Forward</button>
</form>

        </td>
        
    </tr>
    ";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $delete = $_POST['delete'];
    $stmt = $conn->prepare("DELETE FROM departments WHERE name=?");
    $stmt->bind_param('s', $delete);
    $stmt->execute();
 echo "<script>location.href=location.href;</script>";
    exit;
}
                ?>
            </tbody>
        </table>
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

