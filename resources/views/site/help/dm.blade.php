@extends('site.help.partial.template', ['breadcrumb'=>'Direct Messages'])

@section('section')

<div class="title">
  <h3 class="font-weight-bold">{{__('helpcenter.directMessages')}}</h3>
</div>
<hr>
<p class="lead ">Send and recieve direct messages from other profiles.</p>
<hr>
<p>
  <a class="text-dark font-weight-bold" data-toggle="collapse" href="#collapse1" role="button" aria-expanded="false" aria-controls="collapse1">
    <i class="fas fa-chevron-down mr-2"></i>
    How do I use negarin Direct?
  </a>
  <div class="collapse" id="collapse1">
    <div>
      <p>negarin Direct lets you send messages to another account. You can send the following things as a message on negarin Direct:</p>
      <ul>
        <li>
          Photos or videos you take or upload from your library
        </li>
        <li>
          Posts you see in feed
        </li>
        <li>
          Profiles
        </li>
        <li>
          Text
        </li>
        <li>
          Hashtags
        </li>
        <li>
          Locations
        </li>
      </ul>
      <p>To see messages you've sent with negarin Direct, tap <i class="far fa-comment-dots"></i> in the top right of feed. From there, you can manage the messages you've sent and received.</p>
      <p>Photos or videos sent with negarin Direct can't be shared through negarin to other sites like Mastodon or Twitter, and won't appear on hashtag and location pages.</p>
    </div>
  </div>
</p>
{{-- <p> 
  <a class="text-dark font-weight-bold" data-toggle="collapse" href="#collapse2" role="button" aria-expanded="false" aria-controls="collapse2">
    <i class="fas fa-chevron-down mr-2"></i>
    How do I manage messages I've recieved with negarin Direct?
  </a>
  <div class="collapse" id="collapse2">
    <div>
      
    </div>
  </div>
</p> --}}
<p> 
  <a class="text-dark font-weight-bold" data-toggle="collapse" href="#collapse3" role="button" aria-expanded="false" aria-controls="collapse3">
    <i class="fas fa-chevron-down mr-2"></i>
    How do I unsend a message I've sent using negarin Direct?
  </a>
  <div class="collapse" id="collapse3">
    <div class="mt-2">
      You can click the message and select the <strong>Delete</strong> option.
    </div>
  </div>
</p>
<p> 
  <a class="text-dark font-weight-bold" data-toggle="collapse" href="#collapse4" role="button" aria-expanded="false" aria-controls="collapse4">
    <i class="fas fa-chevron-down mr-2"></i>
    Can I use negarin Direct to send messages to people I’m not following?
  </a>
  <div class="collapse" id="collapse4">
    <div class="mt-2">
      You can send a message to someone you are not following though it may be sent to their filtered inbox and not easily seen.
    </div>
  </div>
</p>
<p> 
  <a class="text-dark font-weight-bold" data-toggle="collapse" href="#collapse5" role="button" aria-expanded="false" aria-controls="collapse5">
    <i class="fas fa-chevron-down mr-2"></i>
    How do I report content that I've recieved in a negarin Direct message?
  </a>
  <div class="collapse" id="collapse5">
    <div class="mt-2">
      You can click the message and then select the <strong>Report</strong> option and follow the instructions on the Report page.
    </div>
  </div>
</p>

@endsection













