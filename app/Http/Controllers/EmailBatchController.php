<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\EmailBatch;
use Illuminate\Http\Request;

class EmailBatchController extends Controller
{

    public function index()
    {
        $batches = EmailBatch::query()
            ->select([
                'id',
                'project_id',
                'status',
                'sent_count',
                'failed_count',
                'total_emails',
                'created_at',
                'updated_at'
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('EmailsMonitor', [
            'batches' => $batches ?? [],
            'statusColors' => [
                'pending' => 'bg-yellow-500',
                'processing' => 'bg-blue-500',
                'completed' => 'bg-green-500',
                'failed' => 'bg-red-500',
                'partially_failed' => 'bg-orange-500'
            ]
        ]);
    }
}
