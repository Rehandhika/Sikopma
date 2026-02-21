<?php

use Illuminate\Support\Facades\Route;

// Load Authentication Routes
require __DIR__.'/auth.php';

// Load Public Routes
require __DIR__.'/public.php';

// Load Admin / Management Routes
require __DIR__.'/admin.php';
