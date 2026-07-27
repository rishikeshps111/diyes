<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Leave Types</title><style>
body{font-family:DejaVu Sans,sans-serif;font-size:8px;color:#222}h2{margin:0 0 14px}table{border-collapse:collapse;width:100%}th,td{border:1px solid #aaa;padding:5px;text-align:left;vertical-align:top}th{background:#f1f5f9}
</style></head><body>
<h2>Leave Types</h2>
<table><thead><tr><th>Code</th><th>Name</th><th>Type</th><th>Max/Year</th><th>Carry Forward</th><th>Applicable</th><th>Gender</th><th>Max/Request</th><th>Notice</th><th>Half Day</th><th>Approval</th><th>Encashment</th><th>Status</th></tr></thead><tbody>
@foreach($leaveTypes as $leaveType)<tr>
<td>{{ $leaveType->code }}</td><td>{{ $leaveType->leave_name }}</td><td>{{ ucfirst($leaveType->leave_type) }}</td><td>{{ $leaveType->max_leaves_per_year }}</td><td>{{ $leaveType->carry_forward_allowed ? 'Yes ('.$leaveType->max_carry_forward_limit.')' : 'No' }}</td><td>{{ $leaveType->applicable_for_text }}</td><td>{{ ucfirst($leaveType->gender_specific) }}</td><td>{{ $leaveType->max_leave_days_per_request }}</td><td>{{ $leaveType->advance_notice_days }} day(s)</td><td>{{ $leaveType->allow_half_day?'Yes':'No' }}</td><td>{{ $leaveType->requires_approval?'Yes':'No' }}</td><td>{{ $leaveType->encashment_allowed?'Yes':'No' }}</td><td>{{ $leaveType->status?'Active':'Inactive' }}</td>
</tr>@endforeach
</tbody></table></body></html>
