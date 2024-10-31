<?php 


	$su = get_option( 'sb_username' );
	$sp = get_option( 'sb_password' );

	$sb_selected_web =  json_decode(get_option('sb_selected_web')); 

	 ?>

	 	<?php if($su != ""): ?>

			<?php 

				$ch = curl_init();
				$show_period = get_option('statistics_period');
				
				if( $show_period == '' )
					$show_period = 7;



				$string_to_send = "action=check_secret_key_statistics&sb_username=".$su.'&sb_password='.$sp;

				if( $sb_selected_web->sb_sweb )
					$string_to_send = "action=check_secret_key_statistics&sb_username=".$su.'&sb_password='.$sp.'&sweb='.$sb_selected_web->sb_sweb.'&period='.$show_period;

				curl_setopt($ch, CURLOPT_URL,'http://sexbundle.com/processor');
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS,
				            $string_to_send  );

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				$server_output = curl_exec ($ch);
				curl_close ($ch);
		
				$answer = json_decode($server_output);


				$widgets = $answer->widgets;

			
				$widget_size = sizeof( json_decode(json_encode($widgets),true));



	 ?>

	
	 			<div class="updated fade">

	 					<form method="post">
						<label>Show statistics for </label>				
								<select id="show_period" name="show_period">
									<option <?php if($show_period == 7) echo 'selected="selected"'; ?> value="7">Last 7 Days</option>
									<option  <?php if($show_period == 15) echo 'selected="selected"'; ?> value="15">Last 15 Days</option>
								</select>		
								<input type="submit" value="Show" name="filter_period"/>

							</form>			
	 			</div>

			<?php if( sizeof($answer->statistics) >= 1 ): ?>
						<div class="updated fade" style="overflow:hidden">
						<?php 	$sb_selected_web =  json_decode(get_option('sb_selected_web'));  ?>
						<h2>Your statistics for <?php echo $sb_selected_web->sb_name ?></h2>
						<p style=" margin-top:0px; color:red; font-size:10px; margin-bottom:0px;">In order to receive more traffic, you must submit articles periodically. </p>
						<p style="margin-top:0px; font-size:10px; margin-bottom:0px;">PS: The more traffic you send us the more traffic you will receive for us.</p>


							
							<br/><br/><br/>

													
							<div style="float:left;color:#4572a7;">Total Received from your website: <span id="totalrec">0</span> </div>
							<div style="float:left;margin-left:80px;color:#aa4643;">Total Sent to your website: <span id="totalsent">0</span></div>
							<div style="clear:both"></div>
							<br/><br/>
							<div style="width:700px !important">
								<canvas id="canvas" height="200" width="700"></canvas>
							</div>
							
							<br/><br/>
							<div style="clear:both"></div>
							
						</div>


		<script>

		jQuery(document).ready(function(){
			jQuery('.see_extra_info').click(function(){
				jQuery(this).parent().find('.extra_info').toggle();
				return false;
			});

		});
		function calculate_statistics(){


		var statistics;

				statistics = JSON.parse( '<?php echo json_encode($answer->statistics) ?>' );

				var l = new Array();
				var dataset_sent = new Array();
				var dataset_received = new Array();
				var total_sent = 0;
				var total_received = 0;

				for (var k in statistics){

				    if (statistics.hasOwnProperty(k)) {
				        l.push(k);
				        dataset_sent.push( statistics[k]['sent']);
				        dataset_received.push( statistics[k]['received']);

				        total_sent += statistics[k]['sent'];
				        total_received += statistics[k]['received'];


				    }
				}

				jQuery('#totalrec').html( total_received );
				jQuery('#totalsent').html( total_sent );
				
				var randomScalingFactor = function(){ return Math.round(Math.random()*100)};
				var lineChartData = {
					labels : l,
					datasets : [
						{
							label: "Received Visits",
							fillColor : "rgba(69,114,167,0.2)",
							strokeColor : "rgba(69,114,167,1)",
							pointColor : "rgba(69,114,167,1)",
							pointStrokeColor : "#fff",
							pointHighlightFill : "#fff",
							pointHighlightStroke : "rgba(69,114,167,1)",
							data : dataset_received
						},
						{
							label: "Sent Visits",
							fillColor : "rgba(170,70,67,0.2)",
							strokeColor : "rgba(170,70,67,1)",
							pointColor : "rgba(170,70,67,1)",
							pointStrokeColor : "#fff",
							pointHighlightFill : "#fff",
							pointHighlightStroke : "rgba(170,70,67,1)",
							data : dataset_sent
						}

					]

				}

				var ctx = document.getElementById("canvas").getContext("2d");
				window.myLine = new Chart(ctx).Line(lineChartData, {
					responsive: true
				});
		}



		jQuery(document).ready(function(){

			calculate_statistics();


		});

	</script>


<div class="updated">
	<h2>Top 10 posts in the last <?php echo $show_period  ?> days</h2>
	<?php $i = 1; foreach( $answer->top_posts as $click ): ?>
		<p><span style="font-weight:bold;"><?php echo $i++ ?>.</span> <a target="_blank" href="<?php echo $click->URL ?>"><?php echo $click->post_title ?> [<?php echo $click->ctn ?>]</a></p>
	<?php endforeach; ?>
	

</div>

			<div class="updated fade">
				<h2>Posts that received traffic ( grouped by days )</h2>
				<?php $last_date = ''; ?>

				<?php foreach( $answer->click_tracking as $click ):   ?>
					<?php if( $last_date != $click->DATE): ?>
						<?php $last_date = $click->DATE ?>
						<p style="font-size:14px; font-weight:bold"><?php echo $last_date ?></p> 
						<hr/>
					<?php endif; ?>

					<?php if($click->URL != '' ): ?><p> <a target="_blank" href="<?php  echo $click->URL  ?>"><?php echo $click->post_title ?></a> </p><?php endif; ?>
				<?php endforeach; ?>
				

			</div>
			
			<?php  else: ?>
				<div class="error fade" style="overflow:hidden">
					<p>Statistics</p>
					<p style="color:red"> There are no statistics available at this moment. </p>
				</div>
			<?php endif; ?>

			<?php endif; ?>



