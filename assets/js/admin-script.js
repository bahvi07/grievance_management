$(document).ready(function() {
    // Debug: Check if jQuery and DataTables are loaded
    console.log('jQuery version:', $.fn.jquery);
    console.log('DataTables plugin available:', typeof $.fn.DataTable !== 'undefined');
    
    // Initialize DataTables with error handling
    try {
        // Initialize all tables
        $('#pendingTable').DataTable();
        $('#rejectedTable').DataTable();
        $('#forwardedTable').DataTable();
        console.log('DataTables initialized successfully');
    } catch (error) {
        console.error('Error initializing DataTables:', error);
    }
});


$(document).ready(function() {
    $(document).on('click', '.viewImageBtn', function() {
        const imagePath = $(this).data('image') || 'placeholder.jpg';
        $('#complaintImage').attr('src', imagePath);
    });
});

// forward complaint script
$(document).on('click', '.forwardBtn', function() {
    const category = $(this).data('category');
    const refid = $(this).data('refid');
    const name = $(this).data('name');
    const phone = $(this).data('phone');
    const location = $(this).data('location');
    const image = $(this).data('img');
    $('#forward_name').val(name);
    $('#forward_image').val(image);

    $('#forward_location').val(location);
    $('#forward_phone').val(phone);
    $('#forward_category').val(category);
    $('#forward_refid').val(refid);
    $('#search_area').val('');
    $('#departmentList').html('');
});

// Handle area search
$('#search_area').on('input', function() {
    const area = $(this).val().trim();
    const category = $('#forward_category').val();
    const refid = $('#forward_refid').val();
    const name = $('#forward_name').val();
    const phone = $('#forward_phone').val();
    const location = $('#forward_location').val();
    const image = $('#forward_image').val();
    if (area.length < 2) {
        $('#departmentList').html('');
        return;
    }

    // Get CSRF token specifically from the forward modal or the meta tag
    const csrfToken = $('#forwardModal input[name="csrf_token"]').val() || $('meta[name="csrf-token"]').attr('content');

    if (!csrfToken) {
        console.error('CSRF token not found in meta tag or forward modal.');
        $('#departmentList').html('<div class="text-danger">Security token missing. Please refresh the page.</div>');
        return;
    }

    $.ajax({
        url: './searchDepartement.php',
        method: 'POST',
        data: {
            area,
            category,
            refid,
            name,
            phone,
            location,
            image,
            csrf_token: csrfToken
        },
        success: function(data) {
            $('#departmentList').html(data);
        },
        error: function(xhr, status, error) {
            console.error('Department search error:', error);
            if (xhr.status === 403) {
                $('#departmentList').html('<div class="text-danger">Security validation failed. Please refresh the page and try again.</div>');
            } else {
                $('#departmentList').html('<div class="text-danger">Error searching departments. Please try again.</div>');
            }
        }
    });
});

// cahnge table view
document.addEventListener('DOMContentLoaded', () => {
    const tabMap = {
        newComp: 'pendingTableContainer',
        rejComp: 'rejectedTableContainer',
        forwardComp: 'forwardedTableContainer'
    };

    // Hide all containers except the first one initially
    Object.values(tabMap).forEach((containerId, index) => {
        const container = document.getElementById(containerId);
        if (container) {
            if (index === 0) {
                container.classList.remove('d-none');
            } else {
                container.classList.add('d-none');
            }
        }
    });

    // Add click handlers to all tabs
    Object.keys(tabMap).forEach(tabId => {
        const tabElement = document.getElementById(tabId);
        if (tabElement) {
            tabElement.addEventListener('click', function(e) {
                e.preventDefault();

                // Hide all containers
                Object.values(tabMap).forEach(containerId => {
                    const container = document.getElementById(containerId);
                    if (container) {
                        container.classList.add('d-none');
                    }
                });

                // Remove active class from all tabs
                document.querySelectorAll('#complaintTabs .nav-link').forEach(link => {
                    link.classList.remove('active');
                });

                // Show selected container
                const selectedContainer = document.getElementById(tabMap[tabId]);
                if (selectedContainer) {
                    selectedContainer.classList.remove('d-none');
                    
                    // Reinitialize DataTables for the newly visible table
                    setTimeout(() => {
                        const tableId = tabId === 'newComp' ? 'pendingTable' : 
                                       tabId === 'rejComp' ? 'rejectedTable' : 'forwardedTable';
                        
                        if ($.fn.DataTable.isDataTable('#' + tableId)) {
                            $('#' + tableId).DataTable().draw();
                        }
                    }, 100);
                }

                // Add active class to clicked tab
                this.classList.add('active');
            });
        }
    });
});

