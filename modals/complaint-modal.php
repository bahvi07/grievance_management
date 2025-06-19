<div class="modal fade" id="complaintModal" tabindex="-1" aria-labelledby="complaintModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="complaintForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="complaintModalLabel">Raise a Complaint</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <!-- Form fields here -->
          <!-- Example: -->
          <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <!-- Add others like email, complaint text, department, etc. -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" id="submitComplain" class="btn btn-primary">Submit</button>
        </div>
      </div>
    </form>
  </div>
</div>
