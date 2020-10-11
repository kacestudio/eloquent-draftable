<?php

namespace Kace\Draftable\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DraftableTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_determine_if_a_model_is_draft(): void
    {
        $draft = TestModel::factory()->make();

        $this->assertTrue($draft->isDraft());

        $scheduled = TestModel::factory()
            ->scheduled()
            ->make();

        $this->assertTrue($scheduled->isDraft());
    }

    /** @test */
    public function it_can_determine_if_a_model_is_published(): void
    {
        $published = TestModel::factory()
            ->published()
            ->make();

        $this->assertTrue($published->isPublished());

        $scheduled = TestModel::factory()
            ->scheduled()
            ->make();

        $this->assertFalse($scheduled->isPublished());

        Carbon::setTestNow(Carbon::now()->addDay());

        $this->assertTrue($scheduled->isPublished());
    }

    /** @test */
    public function it_can_retrieves_published_models_by_default()
    {
        TestModel::factory()->create();

        TestModel::factory()
            ->published()
            ->create();

        $models = TestModel::all();

        $this->assertCount(1, $models);
        $this->assertTrue(
            $models
                ->first()
                ->isPublished()
        );
    }

    /** @test */
    public function it_can_retrives_models_with_drafts()
    {
        TestModel::factory()->create();

        TestModel::factory()
            ->published()
            ->create();

        $models = TestModel::withDrafts()->get();
        $draftModel = TestModel::onlyDrafts()->get();
        $publishedModel = TestModel::all();

        $this->assertCount(2, $models);
        $this->assertCount(1, $draftModel);
        $this->assertCount(1, $publishedModel);
    }

    /** @test */
    public function it_can_retrives_draft_models_only()
    {
        TestModel::factory()->create();

        TestModel::factory()
            ->published()
            ->create();

        $models = TestModel::onlyDrafts()->get();

        $this->assertCount(1, $models);
        $this->assertTrue(
            $models
                ->first()
                ->isDraft()
        );
    }

    /** @test */
    public function it_can_mark_a_model_as_published()
    {
        $model = TestModel::factory()->make();

        $this->assertFalse($model->isPublished());

        $model->publish();

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_can_mark_a_model_as_published_without_saving()
    {
        $model = TestModel::factory()->make();

        $this->assertFalse($model->isPublished());

        $model->setPublished(true);

        $this->assertTrue($model->isPublished());

        $this->assertTrue($model->isDirty());
    }

    /** @test */
    public function it_can_mark_a_model_as_draft()
    {
        $model = TestModel::factory()
            ->published()
            ->make();

        $this->assertFalse($model->isDraft());

        $model->draft();

        $this->assertTrue($model->isDraft());
    }

    /** @test */
    public function it_can_mark_a_model_as_draft_without_saving()
    {
        $model = TestModel::factory()
            ->published()
            ->make();

        $this->assertFalse($model->isDraft());

        $model->setPublished(false);

        $this->assertTrue($model->isDraft());

        $this->assertTrue($model->isDirty());
    }

    /** @test */
    public function it_can_publish_or_draft_a_model_based_on_a_boolean_value()
    {
        $model = TestModel::factory()
            ->make();

        $this->assertTrue($model->isDraft());

        $model->publish(true);

        $this->assertTrue($model->isPublished());

        $model->publish(false);

        $this->assertTrue($model->isDraft());
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published()
    {
        $model = TestModel::factory()->make();

        $model->publishAt($scheduleAt = Carbon::now()->addDays(7));

        $this->assertTrue($model->isDraft());

        Carbon::setTestNow($scheduleAt);

        $this->assertTrue($model->isPublished());
    }

    /** @test */
    public function it_can_schedule_a_model_to_be_published_without_saving()
    {
        $model = TestModel::factory()->make();

        $model->setPublishedAt($scheduleAt = Carbon::now()->addDays(7));

        $this->assertTrue($model->isDraft());
        $this->assertTrue($model->isDirty());

        Carbon::setTestNow($scheduleAt);

        $this->assertTrue($model->isPublished());
        $this->assertTrue($model->isDirty());
    }

    /** @test */
    public function it_can_accept_a_null_publish_date_to_indefinitely_draft_a_model()
    {
        $model = TestModel::factory()
            ->published()
            ->create();

        $model->setPublishedAt(null);

        $this->assertTrue($model->isDraft());
        $this->assertTrue($model->isDirty());

        $model->setPublished(true);

        $model->publishAt(null);

        $this->assertTrue($model->isDraft());
        $this->assertTrue($model->isClean());
    }
}
