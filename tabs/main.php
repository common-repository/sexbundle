<?php 	

	

	$su = get_option( 'sb_username' );
	$sp = get_option( 'sb_password' );
	$sw = get_option( 'sb_sites' );

	$active_all_widget_id = get_option( 'sb_widget_all_id' );

	$sb_selected_web =  json_decode(get_option('sb_selected_web')); 
	$site_categories = 	get_categories();

?>
	<div class="<?php if( $su  ): ?> updated <?php else: ?> error <?php endif; ?> fade" style="overflow:hidden">
			<h2>Sync with your SexBundle account</h2>
			<form method="post" action="" >

				<input type="hidden" name="sb_update_account" value="1"/>	
				<p><label>Sexbundle username: </label><input type="text" size="70" name="sb_username" id="sb_username" value="<?php echo $su  ?>"></p>
				<p><label>Sexbundle password: </label><input type="password" size="70" name="sb_password" id="sb_password" value="<?php echo $sp  ?>"></p>
				<p><input  style=" margin:0 15px; margin-left:0px;" type="submit" value="Login" class="button"></p>

			
			</form>

		</div>
		<?php if($su != ""): ?>

			<?php 

				$ch = curl_init();


				$string_to_send = "action=check_secret_key&sb_username=".$su.'&sb_password='.$sp;

				if( $sb_selected_web->sb_sweb )
					$string_to_send = "action=check_secret_key&sb_username=".$su.'&sb_password='.$sp.'&sweb='.$sb_selected_web->sb_sweb;

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
			<br/><br/>
			<div class="<?php if( $sb_selected_web->sb_sweb  ): ?> updated <?php else: ?> error <?php endif; ?> fade">

				<h2>Select your current website</h2>
				<?php $websites = json_decode($sw)  ?>
				<form method="post" action="">
					<input type="hidden" value="1" name="sb_select_web" />	
					<p>
						<select name="sb_sweb" id="sb_sweb">
							<?php foreach ( $websites as $web ): ?>
								<option value="<?php echo $web->ID ?>" <?php if( $sb_selected_web->sb_sweb == $web->ID ): ?> selected="selected" <?php endif; ?> ><?php echo $web->website ?></option>
							<?php endforeach; ?>
						</select>
					</p>
					<p><input  style=" margin:0 15px; margin-left:0px;" type="submit" value="Select website" class="button"></p>
				</form>
				
	
				<?php if( $sb_selected_web->sb_sweb  ): ?>

					<hr/>
					<p>Site name: <strong><?php echo $sb_selected_web->sb_name  ?></strong></p>
					<p>Site url: <strong><?php echo $sb_selected_web->sb_url  ?></strong></p>

				<?php endif;  ?>
				

			</div>
			<?php if( $widget_size > 0 ) :?>
			<br/><br/>
			<div  class="error fade" ><h2>Manage your widgets below</h2></div>

			<?php foreach( $widgets as $widget): ?>
				<div  class="<?php if( $active_all_widget_id ==  $widget->widget_id   ): ?> updated <?php else: ?> error <?php endif; ?> fade" >
		
							<p>Widget name: <strong><?php echo $widget->widget_name ?></strong></p>
							<p>Widget categories: <strong><?php echo $widget->widget_categories ?></strong></p>
							
							<form method="post" action="">

								<?php if( $active_all_widget_id !=  $widget->widget_id   ): ?> 
									 
									 <p><input  class="button button-primary" style=" margin:0 15px; margin-left:0px;" type="submit" value="Use this widget at the end of every post" ></p>
									 <input type="hidden" name="widget_id_add" value="<?php echo $widget->widget_id ?>" />
								     <input type="hidden" name="widget_id_add_script" value="<?php echo $widget->widget_script ?>" />	
								 <?php else: ?>
								 	<input type="hidden" name="widget_id_remove" value="<?php echo $widget->widget_script ?>" />
									 <p><input  style=" margin:0 15px; margin-left:0px;" type="submit"  value="Remove" class="button"></p> 
								<?php endif; ?>
								
							</form>


							 <?php if( $widget_size  > 1 ): ?>
							 	
							 	<form method="post">
							 		<input type="hidden" name="widget_cat_script" value="<?php echo $widget->widget_script ?>" />
							 		<input type="hidden" name="widget_script_id_ref" value="<?php echo $widget->widget_id ?>" />
							 	
							 	
								<p>OR</p>
								<?php 
									$category_widgets = json_decode(get_option('category_widgets'),true);
					
									if( ! isset($category_widgets[$widget->widget_id])): 

								?>
								<p>
									<select name="widget_script_id">
									 	<?php 	$categories = get_categories(  );  ?>
										<?php foreach( $categories as $cat ): 
											$content_widget = get_option( 'sexcat_'.$cat->term_id );
										?>
											<option <?php if($content_widget != ''): echo 'selected="selected"'; endif; ?> value="<?php echo $cat->term_id ?>"><?php echo $cat->name ?></option>
										<?php endforeach; ?>
									 </select>
									 <input   style=" margin:0 15px; margin-left:0px;" type="submit" value="Use this widget at the end of a specific category" class="button"></p></form>
									<?php else: ?>
										<form method="post">
											<input type="hidden" name="widget_script_id_ref" value="<?php echo $widget->widget_id ?>" />			
											<input type="hidden" name="widget_script_id" value="<?php echo $category_widgets[$widget->widget_id] ?>" />
											<p><input name="remove_from_category"  style=" margin:0 15px; margin-left:0px;" type="submit" value="Remove from category -> <?php $c = get_the_category_by_ID( $category_widgets[$widget->widget_id] );  echo $c; ?> " class="button"></p>	
										</form>
									<?php endif; ?>

								<?php endif; ?>
							<a href="#" class="see_extra_info">+ See widget Code and Preview</a><br/><br/>
							<div class="extra_info" style="display:none">
								<p style="color:red; font-size:10px">* If you have any database or object cache plugin installed, please empty the cache after any change is made.</p>
								<hr/>
								<p>Widget code for manual implementation</p>
								<p style="color:red; font-size:10px">* If you manually implement the widget you can skip this step.</p>
								<textarea style="width:450px; height:170px;  "><?php echo $widget->widget_script ?></textarea>
					

								<hr/>
								<p>Widget preview</p>
								<div style="text-align:left;float:left"><?php echo $widget->widget_script ?></div>
								<div style="clear:both"></div>
							</div>
				

				</div>
			<?php endforeach; ?>

			<?php else: ?>
				<div  class="error fade" ><p>There are no widgets available for this website.</p></div>

			<?php endif; ?>



<script>

jQuery(document).ready(function(){
	jQuery('.see_extra_info').click(function(){
		jQuery(this).parent().find('.extra_info').toggle();
		return false;
	});

});
		

	</script>




		<?php endif; ?>

