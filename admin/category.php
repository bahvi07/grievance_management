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
      Department Category Panel
    </div>
    <div class="">
      Admin, <?= $_SESSION['admin_name'] ?? 'Admin' ?>
    </div>
  </div>
  <div class="dept-center row mt-4 ">
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

    <div class="row mb-4 align-items-end">
      <!-- Buttons Container -->
      <div class="col-md-6 d-flex align-items-end">
        <div class="ms-2">
          <label class="form-label d-block invisible">Add</label> <!-- Spacer label -->
          <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#categoryModal">
            <i class="fas fa-plus me-2"></i>Add Category
          </button>
        </div>
      </div>
    </div>

    <!-- Complaints Table -->
    <div class="table-responsive bg-light rounded shadow-sm p-3">
      <div id="deptTableContainer">
        <table id="departmentTable" class="table table-hover table-borderless">
          <thead class="table-dark">
            <tr>
              <th>Sr No.</th>
              <th>Category</th>
              <th>Status</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php

            $stmt = $conn->prepare("SELECT * FROM dept_category");
            $stmt->execute();
            $result = $stmt->get_result();
            $id = 1;

            while ($row = $result->fetch_assoc()) {
              echo "
    <tr>
        <td>" . $id++ . "</td>
        <td>" . htmlspecialchars($row['name']) . "</td>
        <td>
          " . (
            $row['status'] == 1
              ? "<span class='badge bg-success'>Active</span>"
              : "<span class='badge bg-danger'>Inactive</span>"
          ) . "
        </td>
        <td>
          <button 
            class='btn btn-warning border btn-sm edit-btn' 
            data-bs-toggle='modal' 
            data-bs-target='#editCategoryModal'
            data-id='" . htmlspecialchars($row['id']) . "'
            data-category='" . htmlspecialchars($row['name']) . "'
            data-status='" . htmlspecialchars($row['status']) . "'
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

        </td>
    </tr>
    ";
            }

            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

  <!-- Add Category Modal -->
  <div class="modal fade" id="categoryModal" tabindex="-1" aria-labelledby="addCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title" id="addCategoryLabel">Add New Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="POST" id="addCategoryForm">
          <?= csrf_field() ?>
          <div class="modal-body">
            <div class="mb-3">
              <label for="categoryName" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="categoryName" name="category" required maxlength="100" placeholder="Enter category name">
            </div>
            <div class="mb-3">
              <label for="categoryStatus" class="form-label">Status</label>
              <select class="form-select " id="categoryStatus" name="status" required>
                <option value="1" selected>Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="add" class="btn btn-danger">Add Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Edit Category Modal -->
  <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content border-0 shadow">
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title" id="editCategoryLabel">Edit Category</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form action="" method="POST" id="editCategoryForm">
          <?= csrf_field() ?>
          <div class="modal-body">
            <input type="hidden" name="id" id="editCategoryId">
            <div class="mb-3">
              <label for="editCategoryName" class="form-label">Category Name</label>
              <input type="text" class="form-control" id="editCategoryName" name="category" required maxlength="100">
            </div>
            <div class="mb-3">
              <label for="editCategoryStatus" class="form-label">Status</label>
              <select class="form-select " id="editCategoryStatus" name="status" required>
                <option value="1">Active</option>
                <option value="0">Inactive</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-warning text-white">Update Category</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <?php
  include '../includes/admin-footer.php';
  ?>
  
  <!-- Category Management JavaScript -->
  <script src="<?php echo BASE_URL; ?>assets/js/category.js"></script>

</body>

</html>