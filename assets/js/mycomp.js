  let dt;

function initDataTable() {
  if (window.innerWidth >= 768) {
    dt = new DataTable('#myComplainTable', {
      responsive: true,
      scrollX: true
    });
  } else {
    // Ensure DataTable is destroyed on mobile
    if ($.fn.DataTable.isDataTable('#myComplainTable')) {
      dt.destroy();
    }
    // Add mobile-specific classes
    $('#myComplainTable').addClass('table table-responsive');
  }
}

// Initial setup
initDataTable();

// Handle resize
window.addEventListener('resize', initDataTable);
