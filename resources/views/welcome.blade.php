<!DOCTYPE html>
<html>
    <head>
        <title>NexEp Lite</title>

        <link rel="stylesheet" href="//netdna.bootstrapcdn.com/bootstrap/3.0.2/css/bootstrap.min.css">
        <link href='https://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/countdown/2.6.0/countdown.min.js" type="text/javascript"></script>
       
        <style>
            body {
                font-family: 'Roboto', sans-serif;

                background-color: black;
                background: url(http://i.huffpost.com/gen/3545240/images/o-TELEVISION-facebook.jpg) no-repeat center center fixed; 
                -webkit-background-size: cover;
                -moz-background-size: cover;
                -o-background-size: cover;
                background-size: cover;
            }
            
            .panel {
                margin-top: 50px;
            }

            #left-panel {
                position: absolute;
                left: 50px;
                width: 250px;
            }

            #right-panel {
                position: absolute;
                right: 50px;
                width: 250px;
            }

        </style>
    </head>
    <body>
        @include('common.errors')
        <!-- Watchlist -->
        <div class="panel panel-default" id="left-panel">
            <div class="panel-heading text-center">Watchlist <span class="glyphicon glyphicon-film"></span></div>
            <div class="panel-body">
                <!-- show list -->
                @if(count($shows) > 0)
                    @foreach ($shows as $show)
                        <!-- delete button -->
                        {{ Form::open(array('action' => array('NexEpController@destroy', $show->id))) }}
                            {!! csrf_field() !!}
                            {!! method_field('DELETE') !!}
                            <button type="submit" class="btn btn-danger pull-right"><span class="glyphicon glyphicon-trash"></span></button>
                        {{ Form::close() }} 
                        <!-- show info -->
                        <a href= "{{ $show->tvmazeUrl }}" >{{ $show->name }}</a>
                        <div>{{ date('F d, Y H:i', strtotime($show->airstamp)) }}</div>
                        <hr>
                    @endforeach
                @else
                    You're not watching any shows. Get a life!
                @endif
            </div>
        </div>
        
        <!-- search box -->
        <div class="panel panel-default text-center" id="right-panel">
            <div class="panel-heading">Search shows</div>
            <div class="panel-body">
                {{ Form::open(array('action' => 'NexEpController@search')) }}
                    <div class="input-group">
                        <input type="text" class="form-control" placeholder="Better Call Saul" name="showName">
                        <div class="input-group-btn">
                            <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-search"></span></button>
                        </div>
                    </div>
                {{ Form::close() }}
            </div>
            <!-- search results -->
            @if(!empty($showData['showName']))
                <img src="{{ $showData['showPic'] }}" class="img-responsive center-block"/>
                {{ $showData['showName'] }}<br>
                Status: {{ $showData['showStatus'] }}<br>
                <!-- if a next episode is found -->
                @if(!empty($nextEpData['nextEpSeason']))
                    <hr>
                    <h4>Next episode info</h4> <br>
                    Season {{ $nextEpData['nextEpSeason'] }}<br>
                    Episode {{ $nextEpData['nextEpNumber'] }}<br>
                    NextEp: {{ date('F d, Y H:i', strtotime($nextEpData['nextEpTimestamp'])) }} (LT)<br><br>
                    <!-- add button -->
                    {{ Form::open(array('action' => array('NexEpController@store', 'showID' => $showData['showID'], 'showTvmazeUrl' => $showData['showTvmazeUrl'], 'showName' => $showData['showName'], 'nextEpTimestamp' => $nextEpData['nextEpTimestamp']))) }}
                        <button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-plus"></span> Add to watchlist</button>
                    {{ Form::close() }} <br>
                @else
                    <br> {{ $nextEpData }} 
                @endif
            @else 
                <br> {{ @$showData }}
            @endif
        </div>
    </body>
</html>
