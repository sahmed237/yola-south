<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a listing of the FAQs.
     */
    public function index(Request $request)
    {
        $query = Faq::orderBy('sort_order', 'asc')->orderBy('id', 'desc');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('question', 'like', '%' . $search . '%')
                  ->orWhere('answer', 'like', '%' . $search . '%');
        }

        $faqs = $query->paginate(15)->withQueryString();

        return view('admin.faqs.index', compact('faqs'));
    }

    /**
     * Show the form for creating a new FAQ.
     */
    public function create()
    {
        return view('admin.faqs.create');
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'sort_order' => 'required|integer|min:0',
            'is_published' => 'sometimes|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');

        Faq::create($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ successfully created!');
    }

    /**
     * Show the form for editing the specified FAQ.
     */
    public function edit($id)
    {
        $faq = Faq::findOrFail($id);
        return view('admin.faqs.edit', compact('faq'));
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
            'sort_order' => 'required|integer|min:0',
            'is_published' => 'sometimes|boolean',
        ]);

        $validated['is_published'] = $request->has('is_published');

        $faq->update($validated);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ successfully updated!');
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ successfully deleted!');
    }
}
