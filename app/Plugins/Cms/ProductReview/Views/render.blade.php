{{-- review --}}
@php
    $points = (new App\Plugins\Cms\ProductReview\Models\PluginModel)->getPointProduct($product->id);
    $pathPlugin = (new App\Plugins\Cms\ProductReview\AppConfig)->pathPlugin;
@endphp
<section class="section section-sm bg-default">
         <div class="container"  id="review">
            <div id="review-detail">
                  @if ($points->count())
                     @foreach ($points as $k => $point)
                     <div class="review-detail" >
                        <div class="r-name"><b>{{ $point->name }}</b> 
                           <span class="review-star">({{ $point->created_at }}
                              @for ($i = 1;  $i <= $point->point; $i++)
                                 <i class="fa fa-star voted" aria-hidden="true"></i>
                              @endfor
                              @for ($k = 1;  $k <= (5- $point->point); $k++)
                                 <i class="fa fa-star-o" aria-hidden="true"></i>
                              @endfor
                              )</span>
                              @if (auth()->user() && $point->customer_id == auth()->user()->id)
                              <span class="r-remove text-danger text-right btn"  data-id="{{ $point->id }}"><i class="fa fa-trash-o" aria-hidden="true"></i></span>
                              @endif
                        </div>
                        <div class="r-comment">{!! sc_html_render($point->comment) !!}</div>
                     </div>
                     @endforeach
                  @else
                     <p> {{ trans($pathPlugin.'::lang.no_review') }}</p>
                  @endif
                  
            </div>
            <h2 class="title-review">{{ trans($pathPlugin.'::lang.write_review') }}</h2>
              <form class="form-horizontal" id="form-review" method="POST" action="{{ sc_route('product_review.postReview') }}">
               @csrf
               <input type="hidden" name="product_id" value="{{ $product->id }}">
               <div class="form-group required">
                  <div class="col-sm-12">
                    <label class="control-label" for="input-name">{{ trans($pathPlugin.'::lang.your_name') }}</label>
                    <input disabled type="text"  value="{{ auth()->user()?auth()->user()->name:trans($pathPlugin.'::lang.guest') }}" id="input-name" class="form-control">
                  </div>
                </div>
                <div class="form-group required {{ $errors->has('comment') ? ' has-error' : '' }}">
                  <div class="col-sm-12">
                    <label class="control-label" for="input-review">{{ trans($pathPlugin.'::lang.your_review') }}</label>
                    <textarea {{  auth()->user()?'':'disabled' }} name="comment" rows="5" id="input-review" class="form-control">{!! old('comment') !!}</textarea>
                  </div>
                  @if ($errors->has('comment'))
                  <span class="help-block">
                      <i class="fa fa-info-circle" aria-hidden="true"></i> {{ $errors->first('comment') }}
                  </span>
                  @endif
                </div>
                <div class="form-group required {{ $errors->has('point') ? ' has-error' : '' }}">
                  <div class="col-sm-12" >
                    <label class="control-label">{{ trans($pathPlugin.'::lang.rating') }}</label>
                    &nbsp;&nbsp;&nbsp; {{ trans($pathPlugin.'::lang.bad') }}&nbsp;
                    <input type="radio" {{ (old('point') == 1)?'checked':'' }} name="point" value="1" {{  auth()->user()?'':'disabled' }}>
                    &nbsp;
                    <input type="radio" {{ (old('point') == 2)?'checked':'' }} name="point" value="2" {{  auth()->user()?'':'disabled' }}>
                    &nbsp;
                    <input type="radio" {{ (old('point') == 3)?'checked':'' }} name="point" value="3" {{  auth()->user()?'':'disabled' }}>
                    &nbsp;
                    <input type="radio" {{ (old('point') == 4)?'checked':'' }} name="point" value="4" {{  auth()->user()?'':'disabled' }}>
                    &nbsp;
                    <input type="radio" {{ (old('point') == 5)?'checked':'' }} name="point" value="5" {{  auth()->user()?'':'disabled' }}>
                    &nbsp;{{ trans($pathPlugin.'::lang.good') }}</div>
                    @if ($errors->has('point'))
                    <span class="help-block">
                        <i class="fa fa-info-circle" aria-hidden="true"></i> {{ $errors->first('point') }}
                    </span>
                    @endif
                </div>
                @if (sc_captcha_method() && in_array('review', sc_captcha_page()) && view()->exists(sc_captcha_method()->pathPlugin.'::render'))
                  @php
                     $titleButton = trans($pathPlugin.'::lang.submit');
                     $idForm = 'form-review';
                     $idButtonForm = 'button-review';
                  @endphp
                  @include(sc_captcha_method()->pathPlugin.'::render')
                @endif
                <div class="buttons clearfix">
                  <div class="pull-right">
                     <button type="button" id="button-review" data-loading-text="Loading..."
                     class="btn btn-primary">{{ trans($pathPlugin.'::lang.submit') }}
                  </button>
                  </div>
                </div>
               </form>
</div>
</section>
{{-- //end review --}}
