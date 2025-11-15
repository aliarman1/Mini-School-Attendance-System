<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),
            'date' => $this->date?->format('Y-m-d'),
            'status' => $this->status,
            'note' => $this->note,
            'recorded_by_id' => $this->recorded_by_id,
            'recorded_by' => $this->whenLoaded('recordedBy', function () {
                return [
                    'id' => $this->recordedBy->id,
                    'name' => $this->recordedBy->name,
                    'email' => $this->recordedBy->email,
                ];
            }),
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
