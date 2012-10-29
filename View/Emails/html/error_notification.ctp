<h1><?php echo __('An Error happened'); ?>:</h1>

<br />

<?php echo __('Code: %s', h($code)); ?><br />
<?php echo __('File: %s', h($file)); ?><br />
<?php echo __('Line: %d', h($line)); ?><br />

<hr><br />

<h2> <?php echo __('Description'); ?>:</h2>
<?php echo $description; ?>

<hr><br />

<h2><?php echo __('Context'); ?>:</h2>
<?php echo $context; ?>

<hr><br />

<h2><?php echo __('Trace'); ?>:</h2>
<pre>
	<?php echo $trace; ?>
</pre>

<hr><br />

<h2><?php echo __('Session Data'); ?>:</h2>
<pre>
	<?php echo print_r($session); ?>
</pre>

<hr><br />

<h2><?php echo __('Server Data'); ?>:</h2>
<pre>
	<?php echo print_r($server); ?>
</pre>

<hr><br />

<h2><?php echo __('Request Data'); ?>:</h2>
<pre>
	<?php echo print_r($request); ?>
</pre>