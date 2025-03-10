<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <base href="../" />
  <style type="text/css">/* Copyright 2018 PeepSo. All Rights Reserved. */
body {
  color: black;
  font-family: Arial, sans-serif;
  font-size: 11pt;
  margin: 15px auto;
  width: 860px;
}

.nav {
  float: left;
  width: 200px;
}

.nav ul {
  margin: 10px 0 0 0;
  padding: 0;
}

.nav li {
  display: block;
  font-size: 15px;
  list-style: none;
  padding: 5px 10px;
  width: 160px;
}

.nav .selected {
  background: #EEE;
  font-weight: bold;
}

h1 {
  border-bottom: 1px solid #CCC;
  padding-bottom: 10px;
}

.contents {
  padding-left: 210px;
}

a, a:visited {
  color: #10208C;
  text-decoration: none;
}

.meta {
  color: #888;
  font-size: 13px;
}

.block {
  margin: 20px 0;
}

.warning {
  background: #FEE;
  border: 1px solid #A00;
}

.user {
  color: #10208C;
  margin-right: 4px;
}

.comment {
  background: #EEE;
  margin: 5px 0;
  padding: 10px;
}

.thread {
  border: 1px solid #CCC;
  margin: 0 0 20px 0;
  padding: 10px;
}

.message_header {
  border-top: 1px solid #CCC;
  margin: 10px 0 0 0;
  padding: 10px 0 0 0;
}

.message_header .meta {
  float: right;
}

ul {
  list-style: none;
  padding: 0;
}

th {
  font-weight: normal;
  padding: 5px 5px;
  text-align: left;
  vertical-align: top;
  width: 150px;
}

td {
  padding: 5px 5px;
}

.footer {
  clear: both;
  color: #888;
  font-size: 13px;
  margin-top: 10px;
  text-align: center;
}

.warning {
  color: red;
}
</style>
<title><?php echo sprintf(__("Conversation with %s", 'peepso-core'), $participants)?></title></head>
<body>
  <a href="html/messages.htm"><?php echo esc_attr__('Back', 'peepso-core'); ?></a>
  <br /><br />
  <div class="thread">
    <h3><?php echo sprintf(__("Conversation with %s", 'peepso-core'), $participants)?></h3>
    <?php echo esc_attr__('Participants:', 'peepso-core'); ?> <?php echo $participants?>
    <?php
		$ids = array();

		$PeepSoMessages = PeepSoMessages::get_instance();
		$msg_id = $message->ID;

		if ($PeepSoMessages->has_messages_in_conversation(compact('msg_id'))) {
			while ($PeepSoMessages->get_next_message_in_conversation()) {
				global $post;

				$PeepSoUser		= PeepSoUser::get_instance($post->post_author);
				?>
			<div class="message">
		    	<div class="message_header">
		    		<span class="user"><?php echo $PeepSoUser->get_fullname(); ?></span>
		    		<span class="meta"><?php echo $post->post_date ?></span>
		    	</div>
		    </div>
		    <p><?php echo $post->post_content ?></p>
				<?php
			}
		}
    ?>
  </div>
</body>
</html>