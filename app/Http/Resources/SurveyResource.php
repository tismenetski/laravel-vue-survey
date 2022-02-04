<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;
use Nette\Utils\DateTime;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
          'id' => $this->id,
          'title' => $this->slug,
          'slug' => $this->slug,
          'image_url' => $this->image ? Url::to($this->image) : null,
          'status' => $this->status !== 'draft',
          'description' => $this->description,
          'created_at' => (new DateTime($this->created_at))->format('Y-m-d H:i:s'),
          'updated_at' => (new DateTime($this->updated_at))->format('Y-m-d H:i:s'),
          'expire_date' => $this->expire_date,
          'questions' => SurveyQuestionResource::collection($this->questions)
        ];
    }
}
