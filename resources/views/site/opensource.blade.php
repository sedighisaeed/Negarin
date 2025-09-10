@extends('site.partial.template')

@section('section')

  <div class="title">
    <h3 class="font-weight-bold">Open Source</h3>
  </div>
  <hr>
  <section>
    <p class="lead">{{__('site.the_software_that_powers_this_website_is_called')}} <a href="https://negarin.org">negarin</a> {{__('site.and_anyone_can')}} <a href="https://github.com/negarin/negarin">{{__('site.download')}}</a> {{__('site.opensource.or')}} <a href="https://github.com/negarin/negarin">{{__('site.view')}}</a> {{__('site.the_source_code_and_run_their_own_instance')}}</p>
  </section>
@endsection

@push('meta')
<meta property="og:description" content="{{__('site.open_source_in_negarin')}}">
@endpush













