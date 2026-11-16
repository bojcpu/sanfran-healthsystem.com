<?php

use App\Support\DoctorFaqCatalog;

it('covers every staff module with unique questions and valid related links', function () {
    $catalog = (new DoctorFaqCatalog)->toArray();
    $questions = collect($catalog['questions']);
    $ids = $questions->pluck('id');
    $categoryIds = collect($catalog['categories'])->pluck('id');

    expect($catalog['welcome'])->not->toBeEmpty()
        ->and($categoryIds)->toHaveCount(14)
        ->and($ids->unique()->count())->toBe($ids->count())
        ->and($questions->count())->toBeGreaterThanOrEqual(35);

    expect($questions->pluck('category')->unique()->sort()->values()->all())
        ->toEqual($categoryIds->sort()->values()->all());

    $questions->each(function (array $question) use ($ids, $categoryIds) {
        expect($categoryIds)->toContain($question['category'])
            ->and($question['question'])->not->toBeEmpty()
            ->and($question['answer'])->not->toBeEmpty()
            ->and($question['related'])->not->toBeEmpty();

        foreach ($question['related'] as $relatedId) {
            expect($ids)->toContain($relatedId)
                ->and($relatedId)->not->toBe($question['id']);
        }
    });
});

it('answers core system questions about roles, patients, announcements, sms, and reports', function () {
    $questions = collect((new DoctorFaqCatalog)->toArray()['questions']);
    $byId = $questions->keyBy('id');

    expect($byId->get('what-is-the-system')['answer'])->toContain('Barangay San Francisco')
        ->and($byId->get('what-are-the-roles')['answer'])->toContain('Barangay Health Worker')
        ->and($byId->get('how-are-patient-codes-created')['answer'])->toContain('SF-2026-0001')
        ->and($byId->get('when-does-announcement-appear')['answer'])->toContain('Active')
        ->and($byId->get('who-can-send-sms')['answer'])->toContain('System Admins and Midwives')
        ->and($byId->get('what-reports-exist')['answer'])->toContain('Excel')
        ->and($byId->get('how-to-install-pwa')['question'])->toContain('phone')
        ->and($byId->get('how-to-install-pwa')['answer'])->not->toContain('maroon')
        ->and($byId->get('how-to-install-pwa')['answer'])->not->toContain('PWA');
});

it('explains the system in plain words without colors or app jargon', function () {
    $catalog = (new DoctorFaqCatalog)->toArray();
    $visible = strtolower($catalog['welcome'].' '.collect($catalog['categories'])->pluck('label')->implode(' '));

    foreach ($catalog['questions'] as $question) {
        $visible .= ' '.strtolower($question['question'].' '.$question['answer']);
    }

    expect($visible)->not->toContain('maroon')
        ->and($visible)->not->toContain('theme color')
        ->and($visible)->not->toContain('progressive web app')
        ->and($visible)->not->toContain('pwa')
        ->and($visible)->not->toContain('https')
        ->and($visible)->not->toContain('service worker');
});
