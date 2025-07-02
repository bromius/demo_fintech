<?php

app()->get('/', [new \App\Controllers\HomeController(), 'index']);

app()->get('/api/rates', [new \App\Controllers\RatesController(), 'index']);
app()->get('/api/accounts', [new \App\Controllers\AccountsController(), 'index']);
app()->get('/api/chart', [new \App\Controllers\ChartController(), 'index']);

app()->get('/api/transactions', [new \App\Controllers\TransactionsController(), 'index']);
app()->post('/api/transactions/upload', [new \App\Controllers\TransactionsController(), 'upload']);
app()->patch('/api/transactions/update/:id', [new \App\Controllers\TransactionsController(), 'update']);
app()->delete('/api/transactions/delete/:id', [new \App\Controllers\TransactionsController(), 'delete']);