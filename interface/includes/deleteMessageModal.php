<!-- Add this modal HTML right after the media modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete <span id="deleteTitle"></span>?
            </div>
            <div class="modal-footer">
                <form method="POST" id="deleteForm">
                    <input type="hidden" name="resource_id" id="deleteResourceId">
                    <input type="hidden" name="delete_media" value="1">
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>


<!-- Modify the script section to include delete modal handling -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Existing edit button handling code
        const editButtons = document.querySelectorAll('.edit-media');
        editButtons.forEach(button => {
            button.addEventListener('click', function() {
                const mediaData = JSON.parse(this.dataset.media);
                
                // Populate form fields
                document.getElementById('resourceId').value = mediaData.resource_id;
                document.getElementById('title').value = mediaData.title;
                document.getElementById('format').value = mediaData.format;
                document.getElementById('runtime').value = mediaData.runtime;
                document.getElementById('media_type').value = mediaData.media_type;
                document.getElementById('category').value = mediaData.category;
                document.getElementById('accession_number').value = mediaData.accession_number;
                
                // Update modal title
                document.getElementById('mediaModalLabel').textContent = 'Edit Media Resource';
            });
        });

        // Delete modal handling
        const deleteButtons = document.querySelectorAll('.btn-danger');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent immediate form submission
                const mediaData = JSON.parse(this.closest('tr').querySelector('.edit-media').dataset.media);
                
                // Set hidden input and title in delete modal
                document.getElementById('delete-resource-id').value = mediaData.resource_id;
                document.getElementById('delete-media-title').textContent = mediaData.title;
                
                // Show delete confirmation modal
                const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
                deleteModal.show();
            });
        });

        // Reset form when media modal is closed
        const mediaModal = document.getElementById('mediaModal');
        mediaModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('mediaForm').reset();
            document.getElementById('resourceId').value = '';
            document.getElementById('mediaModalLabel').textContent = 'Add New Media Resource';
        });
    });
</script>