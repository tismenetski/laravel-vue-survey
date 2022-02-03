<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveyResource;
use App\Models\Survey;
use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;


class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        $user =  $request->user();

        return SurveyResource::collection(Survey::where('user_id', $user->id)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreSurveyRequest  $request
     * @return SurveyResource
     */
    public function store(StoreSurveyRequest $request)
    {

        $data = $request->validated();

        // check for image in the validated request data, if image exists get the path of the file and store it in the survey model,
        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }

        $survey = Survey::create($data);


        // Create new questions
        foreach ($data['questions'] as $question ) {
            $question['survey_id'] = $survey->id;
            $this->createQuestion($question);
        }

        return new SurveyResource($survey);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Survey  $survey
     * @return SurveyResource
     */
    public function show(Survey $survey, Request $request)
    {
        $user=  $request->user();
        if ($user->id !== $survey->user_id) {
            return abort(403,'Unauthorized action.');
        }
        return new SurveyResource($survey);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateSurveyRequest  $request
     * @param  \App\Models\Survey  $survey
     * @return SurveyResource
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $data = $request->validated();
        // check for image in the validated request data, if image exists get the path of the file and store it in the survey model,
        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            // Save new image
            $data['image'] = $relativePath;

            // Delete Old image
            if ($survey->image) {
                $absolutePath = public_path($survey->image);
                File::delete($absolutePath);
            }


        }

        $survey->update($data);


        // get the ids of existing questions as plain array
            $existingIds = $survey->questions()->pluck('id')->toArray();

        // get the ids of new questions as plain array
            $newIds = Arr::pluck($data['questions'], 'id');

        //find questions to delete
            $toDelete = array_diff($existingIds,$newIds);
        //find questions to add
            $toAdd = array_diff($newIds, $existingIds);
        // Delete question needed to be deleted
            SurveyQuestion::destroy($toDelete);
        // Add new questions
            foreach ($data['questions'] as $question) {
                if (in_array($question['id'], $toAdd)) {
                    $question['survey_id'] = $survey->id;
                    $this->createQuestion($question);
                }
            }

        // Update existing questions
            $questionMap = collect($data['questions'])->keyBy('id');

            foreach ($survey->questions as $question) {
                if (isset($questionMap[$question->id])) {
                    $this->updateQuestion($question, $questionMap[$question->id]);
                }
            }



        return new SurveyResource($survey);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Survey  $survey
     * @return \Illuminate\Http\Response
     */
    public function destroy(Survey $survey, Request $request)
    {
        $user  = $request->user();

        if ($user->id !== $survey->user_id) {
            return abort(403,'Unauthorized action.');
        }

        // Delete Old image
        if ($survey->image) {
            $absolutePath = public_path($survey->image);
            File::delete($absolutePath);
        }

        $survey->delete();
        return response('', 204);
    }

    private function saveImage($image)
    {
        if (preg_match('/^data:image\/(\w+);base64,/',$image , $type)){

            $image = substr($image, strpos($image,',')+ 1);
            $type = strtolower($type[1]); // jpg png gif

            if (!in_array($type, ['jpg', 'gif', 'png', 'jpeg'])) {
                throw new \Exception('invalid image type');
            }

            $image = str_replace(' ' , '+' , $image);
            $image = base64_decode($image);

            if ($image === false) {
                throw new \Exception('base64_decode failed');
            }


        } else {
            throw new \Exception('did not match data URI with image data');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;
        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath,0755,true);
        }

        file_put_contents($relativePath,$image);

        return $relativePath;

    }

    private function createQuestion($data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);

            $validator = Validator::make($data,[
                'question' => 'required|string',
               'type' => ['required',Rule::in([
                   Survey::TYPE_CHECKBOX,
                   Survey::TYPE_RADIO,
                   Survey::TYPE_SELECT,
                   Survey::TYPE_TEXT,
                   Survey::TYPE_TEXTAREA
               ])],
                'description' => 'nullable|string',
                'data' => 'present',
                'survey_id' => 'exists:App\Models\Survey,id'
            ]);

            return SurveyQuestion::create($validator->validated());
        }
    }

    private function updateQuestion(SurveyQuestion $question, $data)
    {
        if (is_array($data['data'])){
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data,[
            'id' => 'exists:App\Models\SurveyQuestion,id',
            'question' => 'required|string',
            'type' => ['required', Rule::in([
                Survey::TYPE_CHECKBOX,
                Survey::TYPE_RADIO,
                Survey::TYPE_SELECT,
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA
            ])],
            'description' => 'nullable|string',
            'data' => 'present'

        ]);
        return $question->update($validator->validated());
    }
}
