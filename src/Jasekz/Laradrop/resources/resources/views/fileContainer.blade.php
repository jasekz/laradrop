<div class="laradrop-thumbnail thumbnail laradrop-droppable col-md-2 laradrop-draggable "  file-id="[[id]]">
    <div class=" well" >
        <h4 class="laradrop-filealias" >[[alias]]</h4>
        <p class="text-info">[[type]] / [[updated_at]]</p>
        <p>
            <a href="#" onclick="return false;" class="label label-success laradrop-file-insert" rel="tooltip" title="{{ trans('laradrop::app.select') }}">{{ trans('laradrop::app.select') }}</a>
            <a href="{{ route('laradrop.index') }}" class="label label-danger laradrop-file-delete" rel="tooltip" title="{{ trans('laradrop::app.delete') }}">{{ trans('laradrop::app.delete') }}</a>
            <a href="#" onclick="return false;" class="label label-warning move" rel="tooltip" title="{{ trans('laradrop::app.move') }}">{{ trans('laradrop::app.move') }}</a>
        </p>
        <img src="[[fileSrc]]" alt="[[alias]]" >
    </div>
</div>

