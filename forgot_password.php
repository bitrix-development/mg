<?php include 'includes/header.php'; ?>

<h2><?php echo $pageTitle; ?></h2>

<?php if ($error): ?>
    <div class="alert alert--error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert--success"><?php echo $success; ?></div>
<?php endif; ?>

<form> <!-- Existing form markup here --> </form>

<p class="help mt-10"><a class="link" href="/pages/login.php">Back to Login</a></p>

<?php include 'includes/footer.php'; ?>