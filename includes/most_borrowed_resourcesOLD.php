<?php if ($_SESSION['role'] === 'student' || $_SESSION['role'] === 'faculty'): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Top Choices</h5>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h6 class="text-center mb-3 fw-bold">Books</h6>
                        <div class="text-center mb-3">
                            <?php if ($popularResources['books']['cover_image']): ?>
                                <img src="../<?php echo htmlspecialchars($popularResources['books']['cover_image']); ?>" 
                                     class="img-fluid rounded shadow-sm resource-image" 
                                     data-resource-id="<?php echo htmlspecialchars($popularResources['books']['resource_id']); ?>"
                                     data-resource-type="book"
                                     alt="Book Cover"
                                     style="height: 200px; width: auto; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='assets/images/default1.png';">
                            <?php else: ?>
                                <img src="assets/images/default1.png" 
                                     class="img-fluid rounded shadow-sm" 
                                     alt="Default Book Cover"
                                     style="height: 200px; width: auto; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <h6 class="text-center fw-bold mb-1"><?php echo htmlspecialchars($popularResources['books']['title']); ?></h6>
                        <p class="text-center text-muted small mb-0">Borrowed <?php echo $popularResources['books']['count']; ?> times</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h6 class="text-center mb-3 fw-bold">Periodicals</h6>
                        <div class="text-center mb-3">
                            <?php if ($popularResources['periodicals']['cover_image']): ?>
                                <img src="../<?php echo htmlspecialchars($popularResources['periodicals']['cover_image']); ?>" 
                                     class="img-fluid rounded shadow-sm resource-image" 
                                     data-resource-id="<?php echo htmlspecialchars($popularResources['periodicals']['resource_id']); ?>"
                                     data-resource-type="periodical"
                                     alt="Periodical Cover"
                                     style="height: 200px; width: auto; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='assets/images/default1.png';">
                            <?php else: ?>
                                <img src="assets/images/default1.png" 
                                     class="img-fluid rounded shadow-sm" 
                                     alt="Default Periodical Cover"
                                     style="height: 200px; width: auto; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <h6 class="text-center fw-bold mb-1"><?php echo htmlspecialchars($popularResources['periodicals']['title']); ?></h6>
                        <p class="text-center text-muted small mb-0">Borrowed <?php echo $popularResources['periodicals']['count']; ?> times</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-4">
                        <h6 class="text-center mb-3 fw-bold">Media</h6>
                        <div class="text-center mb-3">
                            <?php if ($popularResources['media']['cover_image']): ?>
                                <img src="../<?php echo htmlspecialchars($popularResources['media']['cover_image']); ?>" 
                                     class="img-fluid rounded shadow-sm resource-image" 
                                     data-resource-id="<?php echo htmlspecialchars($popularResources['media']['resource_id']); ?>"
                                     data-resource-type="media"
                                     alt="Media Cover"
                                     style="height: 200px; width: auto; object-fit: cover;"
                                     onerror="this.onerror=null; this.src='assets/images/default1.png';">
                            <?php else: ?>
                                <img src="assets/images/default1.png" 
                                     class="img-fluid rounded shadow-sm" 
                                     alt="Default Media Cover"
                                     style="height: 200px; width: auto; object-fit: cover;">
                            <?php endif; ?>
                        </div>
                        <h6 class="text-center fw-bold mb-1"><?php echo htmlspecialchars($popularResources['media']['title']); ?></h6>
                        <p class="text-center text-muted small mb-0">Borrowed <?php echo $popularResources['media']['count']; ?> times</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Most Borrowed Resources</h5>
        </div>
        <div class="row">
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-3">
                        <h6 class="text-center mb-2 fw-bold">Books</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php if ($popularResources['books']['cover_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($popularResources['books']['cover_image']); ?>" 
                                         class="rounded shadow-sm resource-image" 
                                         data-resource-id="<?php echo htmlspecialchars($popularResources['books']['resource_id']); ?>"
                                         data-resource-type="book"
                                         alt="Book Cover"
                                         style="height: 100px; width: auto; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='assets/images/default1.png';">
                                <?php else: ?>
                                    <img src="assets/images/default1.png" 
                                         class="rounded shadow-sm" 
                                         alt="Default Book Cover"
                                         style="height: 100px; width: auto; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($popularResources['books']['title']); ?></h6>
                                <p class="text-muted small mb-0">Borrowed <?php echo $popularResources['books']['count']; ?> times</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-3">
                        <h6 class="text-center mb-2 fw-bold">Periodicals</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php if ($popularResources['periodicals']['cover_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($popularResources['periodicals']['cover_image']); ?>" 
                                         class="rounded shadow-sm resource-image" 
                                         data-resource-id="<?php echo htmlspecialchars($popularResources['periodicals']['resource_id']); ?>"
                                         data-resource-type="periodical"
                                         alt="Periodical Cover"
                                         style="height: 100px; width: auto; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='assets/images/default1.png';">
                                <?php else: ?>
                                    <img src="assets/images/default1.png" 
                                         class="rounded shadow-sm" 
                                         alt="Default Periodical Cover"
                                         style="height: 100px; width: auto; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($popularResources['periodicals']['title']); ?></h6>
                                <p class="text-muted small mb-0">Borrowed <?php echo $popularResources['periodicals']['count']; ?> times</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-body p-3">
                        <h6 class="text-center mb-2 fw-bold">Media</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php if ($popularResources['media']['cover_image']): ?>
                                    <img src="../<?php echo htmlspecialchars($popularResources['media']['cover_image']); ?>" 
                                         class="rounded shadow-sm resource-image" 
                                         data-resource-id="<?php echo htmlspecialchars($popularResources['media']['resource_id']); ?>"
                                         data-resource-type="media"
                                         alt="Media Cover"
                                         style="height: 100px; width: auto; object-fit: cover;"
                                         onerror="this.onerror=null; this.src='assets/images/default1.png';">
                                <?php else: ?>
                                    <img src="assets/images/default1.png" 
                                         class="rounded shadow-sm" 
                                         alt="Default Media Cover"
                                         style="height: 100px; width: auto; object-fit: cover;">
                                <?php endif; ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;"><?php echo htmlspecialchars($popularResources['media']['title']); ?></h6>
                                <p class="text-muted small mb-0">Borrowed <?php echo $popularResources['media']['count']; ?> times</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Resource Details Modal -->
<div class="modal fade" id="resourceDetailsModal" tabindex="-1" aria-labelledby="resourceDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resourceDetailsModalLabel">Resource Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="resourceDetailsContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Animation Style -->
<style>
    .modal.fade .modal-dialog {
        transition: transform .2s ease-out !important;
    }
    
    .modal.show .modal-dialog {
        transform: none;
    }
</style>

<!-- Resource Details Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resourceImages = document.querySelectorAll('.resource-image');
    resourceImages.forEach(img => {
        img.style.cursor = 'pointer';
        img.addEventListener('click', function() {
            const resourceId = this.dataset.resourceId;
            const resourceType = this.dataset.resourceType;
            fetchResourceDetails(resourceId, resourceType);
        });
    });

    function fetchResourceDetails(resourceId, resourceType) {
        fetch(`../api/get_resource_details.php?resource_id=${resourceId}&type=${resourceType}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    displayResourceDetails(data.resource);
                } else {
                    alert('Error loading resource details');
                }
            })
            .catch(error => console.error('Error:', error));
    }

    function displayResourceDetails(resource) {
        const statusColor = resource.status.toLowerCase() === 'available' 
            ? 'text-success' 
            : resource.status.toLowerCase() === 'borrowed' 
                ? 'text-warning' 
                : 'text-muted';

        let detailsHtml = `
            <div class="text-center mb-4">
                <img src="../${resource.cover_image || 'assets/images/default1.png'}" 
                     class="img-fluid rounded shadow-sm" 
                     style="max-height: 200px;" 
                     alt="Resource Cover">
            </div>
            <div class="resource-details">
                <h6 class="fw-bold">Title:</h6>
                <p>${resource.title}</p>
                <h6 class="fw-bold">Category:</h6>
                <p>${resource.category}</p>
                <h6 class="fw-bold">Status:</h6>
                <p class="fw-bold ${statusColor}">${resource.status.toUpperCase()}</p>`;

        if (resource.author) {
            detailsHtml += `
                <h6 class="fw-bold">Author:</h6>
                <p>${resource.author}</p>
                <h6 class="fw-bold">ISBN:</h6>
                <p>${resource.isbn}</p>
                <h6 class="fw-bold">Publisher:</h6>
                <p>${resource.publisher}</p>
                <h6 class="fw-bold">Edition:</h6>
                <p>${resource.edition}</p>
                <h6 class="fw-bold">Publication Date:</h6>
                <p>${resource.publication_date}</p>`;
        }

        document.getElementById('resourceDetailsContent').innerHTML = detailsHtml;
        
        const modal = new bootstrap.Modal(document.getElementById('resourceDetailsModal'), {
            backdrop: true,
            keyboard: true,
            focus: true
        });
        
        document.getElementById('resourceDetailsModal').addEventListener('show.bs.modal', function () {
            this.style.transition = 'all .2s ease-out';
        });
        
        modal.show();
    }
});
</script>
<?php endif; ?> 