<?php

namespace App\Http\Controllers;

use App\Events\QuestionCreateEvent;
use App\Events\TenderAnswerEvent;
use App\Http\Requests;
use App\Model\Question;
use App\Model\Tender;
use Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class QuestionController extends Controller {
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index($entityName, $id)
    {
        $entityClass = '\App\Model\\'.ucfirst($entityName);
        $entity = $entityClass::find($id);
        switch ($entityName) {
            case 'tender' :
                $tender = $entity;
                break;
            case 'lot' :
                $tender = $entity->tender;
                break;
            case 'item' :
                $tender = $entity->lot->tender;
                break;
        }

        return view('pages.question.list', compact('tender', 'entity'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $organizationWrongId
     * @param $tenderWrongId
     *
     * @return Response
     */
    public function create($entityName, $id)
    {
        $entityClass = '\App\Model\\'.ucfirst($entityName);
        $entity = $entityClass::find($id);
        switch ($entityName) {
            case 'tender' :
                $tender = $entity;
                break;
            case 'lot' :
                $tender = $entity->tender;
                break;
            case 'item' :
                $tender = $entity->lot->tender;
                break;
        }

        if ($entity) {
            return view('pages.question.create', ['entity' => $entity, 'tender' => $tender]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @param          $organizationWrongId
     * @param          $tenderWrongId
     *
     * @return Response
     */
    public function store(Request $request)
    {
        $entityName = $request->input('entity_name');
        $entityClass = '\App\Model\\'.ucfirst($entityName);
        $entity = $entityClass::find($request->input('entity_id'));
        if (!$entity->canQuestion()) {
            abort(403);
        }

        $question = new Question([
            'title' => $request->input('title'),
            'description' => $request->input('question'),
            'organization_id' => Auth::user()->organization->id,
            'organization_to_id' => (int)Tender::find($entity->tender->id)->organization->id,
            'tender_id' => $entity->tender->id,
        ]);
        $entity->questions()->save($question);
        Event::fire(new QuestionCreateEvent($question));

        Session::flash('flash_message', 'Запитання відправлено');

        return redirect()->route('questions.index', [ 'id'=> $entity->id, 'entity' => $entity->type ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function show($id) {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function edit($id) {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int     $id
     *
     * @return Response
     */
    public function update(Request $request, $id) {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     *
     * @return Response
     */
    public function destroy($id) {
        //
    }

    public function answer(Request $request, $id)
    {
        $question = \App\Model\Question::find($id);
        $textAnswer = ($request->input('answer'));
        $question->answer = $textAnswer;
        $question->save();
        Event::fire(new TenderAnswerEvent($question));

        return view('pages.question.component.answer', ['question' => $question]);
    }

    public function showUsersQuestions()
    {
        $organizationId = Auth::user()->organization->id;

        $questions = Question::where('organization_id', $organizationId)->orderBy('created_at', 'desc')->get();

        return view('pages.question.question-list', compact('questions'));
    }

    public function showCustomersQuestions()
    {
        $questions = Auth::user()->organization->myQuestions()->orderBy('created_at', 'desc')->paginate(20);
        return view('pages.question.question-list-customer', compact('questions'));
    }
}
