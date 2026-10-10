<?php
declare(strict_types=1);

// Donation page. Every "Donate Now" button links here; /donate?project=ID preselects a project.

render('donate', [
    'title'           => 'Donate',
    'description'     => 'Donate online to Sri Krishnashraya Educational Trust. Your gift provides food, shelter and education. 80G tax benefits available.',
    'selectedProject' => (int) ($_GET['project'] ?? 0),
    'selectedAmount'  => (int) ($_GET['amount'] ?? 0),
]);
