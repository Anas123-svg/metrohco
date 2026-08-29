<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">{{ __('Add Notification') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form id="ajaxForm" class="modal-form create"
                    action="{{ route('admin.mobile_interface.notification.store') }}" method="post"
                    enctype="multipart/form-data">
                    @csrf

                    <div class="form-group">
                        <label>{{ __('Title') . '*' }}</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter Title">
                        <p id="err_title" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Message') }}</label>
                        <textarea rows="2" class="form-control" name="message" placeholder="Enter Message"></textarea>
                        <p id="err_message" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Button Name') . '*' }}</label>
                        <input type="text" class="form-control" name="button_name" placeholder="Enter Button Name">
                        <p id="err_button_name" class="mt-2 mb-0 text-danger em"></p>
                    </div>

                    <div class="form-group">
                        <label>{{ __('Button Url') . '*' }}</label>
                        <input type="url" class="form-control" name="button_url" placeholder="Enter Button Url">
                        <p id="err_button_url" class="mt-2 mb-0 text-danger em"></p>
                    </div>
                </form>
            </div>


            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                    {{ __('Close') }}
                </button>
                <button id="submitBtn" type="button" class="btn btn-primary btn-sm">
                    {{ __('Save') }}
                </button>
            </div>
        </div>
    </div>
</div>
