<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Edit Slider') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxEditForm" class="modal-form"
                    action="{{ route('admin.mobile_interface.notification.update') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="in_id" name="id">

                    <div class="form-group">
                        <label>{{ __('Title') . '*' }}</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter Title"
                            id="in_title">
                        <p id="editErr_title" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Message') }}</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="Enter Message" id="in_message"></textarea>
                        <p id="editErr_message" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Button Name') . '*' }}</label>
                        <input type="text" class="form-control" name="button_name" placeholder="Enter Button Name"
                            id="in_button_name">
                        <p id="editErr_button_name" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Button URL') . '*' }}</label>
                        <input type="url" class="form-control" name="button_url" placeholder="Enter Button URL"
                            id="in_button_url">
                        <p id="editErr_button_url" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                </form>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button id="updateBtn" type="button" class="btn btn-primary btn-sm">
                    {{ __('Update') }}
                </button>
            </div>
        </div>
    </div>
</div>
