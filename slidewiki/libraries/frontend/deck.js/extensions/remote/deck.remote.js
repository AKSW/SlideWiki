(function($, deck, undefined) {
	var $d = $(document),
		config = {
			server: 'http://deckjs-remote.no.de',
			port: null
		},
		joined = false,
		current_slide = 0,
		socket;

	$[deck].remote = true;

	$[deck]('extend', 'remote', function(o){
		var options = $.extend({}, config, o || {});
		setup(options);
	});

	function setup(options) {
		var is_master = options.master || false;

		key = options.sessionId || new Date().getTime();
		socket = io.connect(options.server, { port: options.port || 5000 });

		socket.on('connect', function(){
			socket.emit('join', { url: window.location.href, key: key, is_master: is_master });
			if (is_master) {
				socket.on('master', function(success){
					console.log('got master');
					console.log(success);
					if (success) {
						$d.bind('deck.change', function(e, prev, next){
var selected_id = $[deck]('getSlide', next).attr('id');							
socket.emit('change', {current: selected_id});
						});
					}
				});

				socket.emit('master');
			} else {
				joined = true;
				console.log('connected as client');
				socket.on('slide', function(current){
					console.log('slide');
					if (joined) {
					var selected_id=current;
console.log(selected_id);
						if (loaded_range.indexOf(selected_id)==-1){
							var overal_index=all_slides.indexOf(selected_id);
							loaded_range=[];
							progressiveLoadSlide(selected_id);
							//correct slide numbers
							$('.deck-status-current').text(overal_index+1);
						}
							window.location.hash=selected_id;					
							//$.deck('go', current);
					}
					current_slide = current;
					console.log(current);
				}).on('notify', function(data){
					console.log('slide change');
					if (data.current) {
console.log(data.current);
						current_slide = data.current;
						var selected_id=current_slide;
						if (loaded_range.indexOf(selected_id)==-1){
							var overal_index=all_slides.indexOf(selected_id);
							loaded_range=[];
							progressiveLoadSlide(selected_id);
							//correct slide numbers
							$('.deck-status-current').text(overal_index+1);
						}
							window.location.hash=selected_id;											
						//$.deck('go', current_slide);
					}
				});
			}

		});

	}

})(jQuery, 'deck');
