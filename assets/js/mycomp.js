// Simple table styling for mobile responsiveness
function initTable() {
  if (window.innerWidth < 768) {
    // Add mobile-specific classes for better mobile display
    $('#myComplainTable').addClass('table table-responsive');
  }
}

// Initial setup
initTable();

// Handle resize
window.addEventListener('resize', initTable);
