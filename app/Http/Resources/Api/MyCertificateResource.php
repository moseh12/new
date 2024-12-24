<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Resources\Json\JsonResource;

class MyCertificateResource extends JsonResource
{
    public function toArray($request): array
    {

        return [
            'id'                              => (int) $this->id,
            'title'                           => $this->title,
            'body'                            => $this->body,
            'instructor_signature'            => getFileLink('170x74', $this->instructor_signature),
            'background_image'                => getFileLink('84x85', $this->background_image),
            'administrator_signature'         => getFileLink('170x74', $this->administrator_signature),
        ];
    }
}
