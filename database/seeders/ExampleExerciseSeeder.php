<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class ExampleExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds. Line numbers are 1-based and point at the wrong code.
     */
    public function run(): void
    {
        $topics = Topic::query()->pluck('id', 'name');

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Mass assignment in het Post-model'],
            [
                'topic_id' => $topics['Eloquent'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use Illuminate\Database\Eloquent\Factories\HasFactory;
                    use Illuminate\Database\Eloquent\Model;
                    use Illuminate\Database\Eloquent\Relations\BelongsTo;
                    use Illuminate\Database\Eloquent\Relations\HasMany;

                    class Post extends Model
                    {
                        use HasFactory;

                        protected $fillable = [
                            'title',
                            'slug',
                            'body',
                            'published_at',
                        ];

                        protected function casts(): array
                        {
                            return [
                                'published_at' => 'datetime',
                            ];
                        }

                        public function author(): BelongsTo
                        {
                            return $this->belongsTo(User::class, 'user_id');
                        }

                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class);
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use Illuminate\Database\Eloquent\Relations\HasMany;
                    use Illuminate\Database\Eloquent\Relations\BelongsTo;
                    use Illuminate\Database\Eloquent\Factories\HasFactory;
                    use Illuminate\Database\Eloquent\Model;

                    class Post extends Model
                    {
                        use HasFactory;

                        protected $guarded = [];

                        protected function casts(): array
                        {
                            return [
                                'published_at' => 'date',
                            ];
                        }

                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class);
                        }

                        public function author(): BelongsTo
                        {
                            return $this->belongsTo(User::class);
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 14,
                'title' => 'Alles is mass assignable',
                'hint' => 'Welke velden mag een schrijver via het formulier invullen, en welke niet?',
                'explanation' => 'Met een lege $guarded mag elk veld via create() of fill() gevuld worden, ook user_id. Wie een extra veld user_id meestuurt, plaatst een post onder de naam van iemand anders. Dezelfde fout in het User-model laat iemand zichzelf admin maken via role. Zet alleen de velden uit het formulier in $fillable.

```php
protected $fillable = [\'title\', \'slug\', \'body\', \'published_at\'];
```',
            ],
            [
                'line' => 19,
                'title' => 'Publicatiemoment zonder tijd',
                'hint' => 'Hoe laat gaat een post live die om 14:00 gepland staat?',
                'explanation' => 'De cast date zet de tijd bij het uitlezen op 00:00. Een post die om 14:00 gepland staat, lijkt daardoor al om middernacht gepubliceerd. published_at is een moment, dus gebruik de cast datetime.

```php
\'published_at\' => \'datetime\',
```',
            ],
            [
                'line' => 30,
                'title' => 'Foreign key afgeleid van de methodenaam',
                'hint' => 'Welke kolom zoekt Laravel als je de relatie author() noemt?',
                'explanation' => 'Zonder tweede argument leidt belongsTo() de foreign key af uit de methodenaam: author() wordt author_id. Die kolom bestaat niet, want posts heeft user_id. Geef de foreign key daarom zelf mee.

```php
return $this->belongsTo(User::class, \'user_id\');
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Een user heeft veel posts en comments'],
            [
                'topic_id' => $topics['Relaties'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use App\Enums\Role;
                    use Illuminate\Database\Eloquent\Relations\HasMany;
                    use Illuminate\Foundation\Auth\User as Authenticatable;

                    class User extends Authenticatable
                    {
                        protected $fillable = [
                            'name',
                            'email',
                            'password',
                        ];

                        protected function casts(): array
                        {
                            return [
                                'password' => 'hashed',
                                'role' => Role::class,
                            ];
                        }

                        public function isAdmin(): bool
                        {
                            return $this->role === Role::Admin;
                        }

                        public function posts(): HasMany
                        {
                            return $this->hasMany(Post::class);
                        }

                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class);
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use App\Enums\Role;
                    use Illuminate\Database\Eloquent\Relations\HasMany;
                    use Illuminate\Foundation\Auth\User as Authenticatable;

                    class User extends Authenticatable
                    {
                        protected $fillable = [
                            'name',
                            'email',
                            'password',
                        ];

                        protected function casts(): array
                        {
                            return [
                                'role' => Role::class,
                                'password' => 'hashed',
                            ];
                        }

                        public function isAdmin(): bool
                        {
                            return $this->role === Role::Admin;
                        }

                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class, 'user_id', 'user_id');
                        }

                        public function posts(): HasMany
                        {
                            return $this->hasMany(Post::class, 'author_id');
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 32,
                'title' => 'Verkeerde local key',
                'hint' => 'In welke kolom van users staat de waarde die in comments.user_id terugkomt?',
                'explanation' => 'Het derde argument van hasMany() is de local key: de kolom in de eigen tabel. Dat is id, niet user_id. De tabel users heeft geen kolom user_id, dus Laravel zoekt naar comments met user_id null en vindt er nooit een. Laat de argumenten weg, dan kiest Laravel zelf user_id en id.

```php
return $this->hasMany(Comment::class);
```',
            ],
            [
                'line' => 37,
                'title' => 'Foreign key die niet bestaat',
                'hint' => 'Hoe heet de kolom in posts die naar de schrijver verwijst?',
                'explanation' => 'Het tweede argument van hasMany() is de foreign key in de tabel posts. Die kolom heet user_id, niet author_id, dus de query geeft een databasefout. Laravel leidt user_id zelf af uit de modelnaam User, dus het argument kan weg.

```php
return $this->hasMany(Post::class);
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Validatie in de StorePostRequest'],
            [
                'topic_id' => $topics['Validatie'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Requests;

                    use App\Models\Post;
                    use Illuminate\Foundation\Http\FormRequest;

                    class StorePostRequest extends FormRequest
                    {
                        public function authorize(): bool
                        {
                            return $this->user()->can('create', Post::class);
                        }

                        public function rules(): array
                        {
                            return [
                                'title' => ['required', 'string', 'max:255'],
                                'slug' => ['required', 'string', 'max:255', 'unique:posts,slug'],
                                'body' => ['required', 'string'],
                                'published_at' => ['nullable', 'date'],
                                'tags' => ['array'],
                                'tags.*' => ['integer', 'exists:tags,id'],
                            ];
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Requests;

                    use App\Models\Post;
                    use Illuminate\Foundation\Http\FormRequest;

                    class StorePostRequest extends FormRequest
                    {
                        public function authorize(): bool
                        {
                            return $this->user()->can('create', Post::class);
                        }

                        public function rules(): array
                        {
                            return [
                                'title' => ['string', 'max:255'],
                                'slug' => ['required', 'string', 'max:255'],
                                'body' => 'required|string',
                                'published_at' => ['nullable', 'date'],
                                'tags' => ['nullable', 'array'],
                                'tags.*' => ['integer'],
                            ];
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 18,
                'title' => 'title mist de required-regel',
                'hint' => 'Wat gebeurt er als het formulier geen titel meestuurt?',
                'explanation' => 'Zonder required is title optioneel. Ontbreekt het veld, dan komt het verzoek door de validatie en faalt pas de insert, omdat de kolom title verplicht is. De schrijver ziet dan een foutpagina in plaats van een melding bij het veld.

```php
\'title\' => [\'required\', \'string\', \'max:255\'],
```',
            ],
            [
                'line' => 19,
                'title' => 'Dubbele slugs komen erdoor',
                'hint' => 'Wat gebeurt er als twee posts dezelfde slug krijgen?',
                'explanation' => 'De kolom slug heeft een unique-index. Zonder unique-regel komt een dubbele slug door de validatie en geeft de database een foutmelding. Met unique:posts,slug krijgt de schrijver een nette melding bij het veld.

```php
\'slug\' => [\'required\', \'string\', \'max:255\', \'unique:posts,slug\'],
```',
            ],
            [
                'line' => 23,
                'title' => 'Tags worden niet gecontroleerd',
                'hint' => 'Wat gebeurt er als iemand tag 9999 meestuurt?',
                'explanation' => 'integer controleert alleen het type, niet of de tag bestaat. Een id dat niet bestaat komt door de validatie en faalt pas bij het koppelen in post_tag. Met exists:tags,id weet je zeker dat elke tag bestaat.

```php
\'tags.*\' => [\'integer\', \'exists:tags,id\'],
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Resource-routes voor posts'],
            [
                'topic_id' => $topics['Routes'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    use App\Http\Controllers\CommentApprovalController;
                    use App\Http\Controllers\DashboardController;
                    use App\Http\Controllers\PostController;
                    use Illuminate\Support\Facades\Route;

                    Route::get('/', [PostController::class, 'index'])->name('home');

                    Route::middleware('auth')->group(function () {
                        Route::get('/dashboard', DashboardController::class)->name('dashboard');

                        Route::resource('posts', PostController::class)->except(['index', 'show']);

                        Route::patch('/comments/{comment}/approve', CommentApprovalController::class)
                            ->middleware('can:approve,comment')
                            ->name('comments.approve');
                    });

                    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    use App\Http\Controllers\PostController;
                    use App\Http\Controllers\DashboardController;
                    use App\Http\Controllers\CommentApprovalController;
                    use Illuminate\Support\Facades\Route;

                    Route::get('/', [PostController::class, 'index'])->name('home');
                    Route::get('/dashboard', DashboardController::class)->name('dashboard');

                    Route::middleware(['auth'])->group(function () {
                        Route::resource('post', PostController::class)->except(['index', 'show']);

                        Route::get('/comments/{comment}/approve', CommentApprovalController::class)
                            ->middleware('can:approve,comment')
                            ->name('comments.approve');
                    });

                    Route::get('/posts/{post:slug}', [PostController::class, 'show'])->name('posts.show');
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 9,
                'title' => 'Dashboard zonder login',
                'hint' => 'Wie kan /dashboard openen?',
                'explanation' => 'De dashboard-route staat buiten de groep met de auth-middleware. Ook bezoekers die niet zijn ingelogd openen de pagina, en $request->user() is dan null. Zet de route in de groep.

```php
Route::middleware(\'auth\')->group(function () {
    Route::get(\'/dashboard\', DashboardController::class)->name(\'dashboard\');
```',
            ],
            [
                'line' => 12,
                'title' => 'Resource-naam in het enkelvoud',
                'hint' => 'Welke URL\'s en routenamen maakt Route::resource() van deze naam?',
                'explanation' => 'Route::resource() gebruikt de naam letterlijk voor de URL\'s en de routenamen. Met post worden dat /post/create en post.store, terwijl de rest van de app /posts en posts.store gebruikt. route(\'posts.store\') geeft dan een fout. Gebruik het meervoud.

```php
Route::resource(\'posts\', PostController::class)->except([\'index\', \'show\']);
```',
            ],
            [
                'line' => 14,
                'title' => 'Goedkeuren met GET',
                'hint' => 'Wat gebeurt er als iemand deze link in een comment of e-mail zet?',
                'explanation' => 'Goedkeuren verandert data, maar een GET-request heeft geen CSRF-bescherming. Een admin die op een link klikt, of een browser die de link vooraf laadt, keurt de comment dan al goed. Gebruik PATCH voor een wijziging.

```php
Route::patch(\'/comments/{comment}/approve\', CommentApprovalController::class)
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'N+1 in de PostController'],
            [
                'topic_id' => $topics['Controllers'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Controllers;

                    use App\Models\Post;
                    use Illuminate\View\View;

                    class PostController extends Controller
                    {
                        public function index(): View
                        {
                            $posts = Post::query()
                                ->where('published_at', '<=', now())
                                ->with('author')
                                ->withCount('comments')
                                ->latest('published_at')
                                ->paginate(15);

                            return view('posts.index', ['posts' => $posts]);
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Controllers;

                    use App\Models\Post;
                    use Illuminate\Http\Request;
                    use Illuminate\View\View;

                    class PostController extends Controller
                    {
                        public function index(Request $request): View
                        {
                            $posts = Post::query()
                                ->where('published_at', '<=', $request->input('until', now()))
                                ->orderByDesc('published_at')
                                ->paginate(15);

                            foreach ($posts as $post) {
                                $post->author_name = $post->author->name;
                                $post->comments_count = $post->comments()->count();
                            }

                            return view('posts.index', compact('posts'));
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 14,
                'title' => 'De bezoeker kiest de datum',
                'hint' => 'Wat ziet een bezoeker die ?until=2099-01-01 achter de URL zet?',
                'explanation' => 'De grens voor gepubliceerde posts komt uit de request. Met een datum in de toekomst ziet iedereen ingeplande posts die nog niet live mogen. Bepaal zelf wat gepubliceerd is en gebruik now().

```php
->where(\'published_at\', \'<=\', now())
```',
            ],
            [
                'line' => 19,
                'title' => 'Een query per schrijver',
                'hint' => 'Hoeveel queries draait deze pagina bij 15 posts?',
                'explanation' => '$post->author laadt de schrijver pas als je hem opvraagt, en dat gebeurt hier voor elke post opnieuw. Bij 15 posts zijn dat 15 extra queries: het N+1-probleem. Laad de schrijvers vooraf met with(). De view kan dan $post->author->name gebruiken en de loop is niet nodig.

```php
->with(\'author\')
```',
            ],
            [
                'line' => 20,
                'title' => 'Tellen in de loop',
                'hint' => 'Wat is het verschil tussen comments() met haakjes en comments zonder?',
                'explanation' => 'comments() met haakjes start een nieuwe query, dus elke post krijgt een eigen COUNT-query. withCount() telt alles in één query en zet het resultaat in comments_count.

```php
->withCount(\'comments\')
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Foreign keys in de comments-migration'],
            [
                'topic_id' => $topics['Migrations'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    use Illuminate\Database\Migrations\Migration;
                    use Illuminate\Database\Schema\Blueprint;
                    use Illuminate\Support\Facades\Schema;

                    return new class extends Migration
                    {
                        public function up(): void
                        {
                            Schema::create('comments', function (Blueprint $table) {
                                $table->id();
                                $table->foreignId('post_id')->constrained()->cascadeOnDelete();
                                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                                $table->text('body');
                                $table->timestamp('approved_at')->nullable();
                                $table->timestamps();
                            });
                        }

                        public function down(): void
                        {
                            Schema::dropIfExists('comments');
                        }
                    };
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    use Illuminate\Database\Migrations\Migration;
                    use Illuminate\Database\Schema\Blueprint;
                    use Illuminate\Support\Facades\Schema;

                    return new class extends Migration {
                        public function up(): void
                        {
                            Schema::create('comments', function (Blueprint $table) {
                                $table->id();
                                $table->integer('post_id');
                                $table->foreignId('user_id')->constrained('user')->cascadeOnDelete();
                                $table->text('body');
                                $table->dateTime('approved_at')->nullable();
                                $table->timestamps();
                            });
                        }

                        public function down(): void
                        {
                            Schema::dropIfExists('comment');
                        }
                    };
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 12,
                'title' => 'Geen foreign key naar posts',
                'hint' => 'Wat gebeurt er met de comments als een post wordt verwijderd?',
                'explanation' => 'integer() maakt alleen een getalkolom. Zonder foreign key accepteert de database een post_id die niet bestaat, en blijven comments achter als de post verdwijnt. Daarbij is posts.id een unsigned bigint en dit een gewone integer. foreignId()->constrained() regelt het type en de koppeling.

```php
$table->foreignId(\'post_id\')->constrained()->cascadeOnDelete();
```',
            ],
            [
                'line' => 13,
                'title' => 'Tabelnaam in het enkelvoud',
                'hint' => 'Hoe heet de tabel waar de users in staan?',
                'explanation' => 'constrained() krijgt hier de tabel user mee, maar die tabel heet users. De migration faalt omdat de tabel niet bestaat. Zonder argument leidt Laravel users zelf af uit user_id.

```php
$table->foreignId(\'user_id\')->constrained()->cascadeOnDelete();
```',
            ],
            [
                'line' => 22,
                'title' => 'down() verwijdert de verkeerde tabel',
                'hint' => 'Welke tabellen bestaan er nog na een rollback?',
                'explanation' => 'up() maakt de tabel comments, maar down() verwijdert comment. Die tabel bestaat niet, dus dropIfExists() doet niets en comments blijft staan. De volgende migrate faalt dan omdat de tabel al bestaat.

```php
Schema::dropIfExists(\'comments\');
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Wie mag deze post bewerken?'],
            [
                'topic_id' => $topics['Policies'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Policies;

                    use App\Models\Post;
                    use App\Models\User;

                    class PostPolicy
                    {
                        public function update(User $user, Post $post): bool
                        {
                            if ($user->isAdmin()) {
                                return true;
                            }

                            return $user->id === $post->user_id;
                        }

                        public function delete(User $user, Post $post): bool
                        {
                            return $this->update($user, $post);
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Policies;

                    use App\Models\User;
                    use App\Models\Post;

                    class PostPolicy
                    {
                        public function update(User $user, Post $post): bool
                        {
                            if ($user->role === 'admin') {
                                return true;
                            }

                            return $user->id === $post->id
                                || $post->published_at === null;
                        }

                        public function delete(User $user, Post $post): bool
                        {
                            return $user->can('update', $post);
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 12,
                'title' => 'Enum vergeleken met een string',
                'hint' => 'Welk type heeft $user->role na de cast in het User-model?',
                'explanation' => 'Het User-model cast role naar de enum Role. $user->role is dus een Role-object en geen string, waardoor === \'admin\' altijd false is. Een admin mag zo niets meer bewerken. Vergelijk met de enum of gebruik de hulpmethode.

```php
if ($user->isAdmin()) {
```',
            ],
            [
                'line' => 16,
                'title' => 'Verkeerd id vergeleken',
                'hint' => 'Welk veld van een post zegt wie hem geschreven heeft?',
                'explanation' => '$post->id is het nummer van de post, niet van de schrijver. User 7 mag zo post 7 bewerken, wie die ook geschreven heeft. Vergelijk met user_id.

```php
return $user->id === $post->user_id;
```',
            ],
            [
                'line' => 17,
                'title' => 'Concepten zijn van iedereen',
                'hint' => 'Wie mag het concept van een ander bewerken?',
                'explanation' => 'Door de || mag elke ingelogde user elk concept bewerken, ook dat van een ander. Of een post gepubliceerd is, zegt niets over wie hem mag aanpassen. Haal de voorwaarde weg. Er blijft dan alleen de controle op user_id over.',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Route model binding met een slug'],
            [
                'topic_id' => $topics['Routes'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Controllers;

                    use App\Models\Post;
                    use Illuminate\View\View;

                    class PostController extends Controller
                    {
                        public function show(Post $post): View
                        {
                            abort_if($post->published_at === null || $post->published_at->isFuture(), 404);

                            $post->load(['author', 'tags']);

                            return view('posts.show', ['post' => $post]);
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Http\Controllers;

                    use App\Models\Post;
                    use Illuminate\View\View;

                    class PostController extends Controller
                    {
                        public function show(Post $postSlug): View
                        {
                            abort_unless($postSlug->exists, 404);

                            $postSlug->loadMissing(['author', 'tags']);

                            return view('posts.show', ['post' => $postSlug]);
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 10,
                'title' => 'Argument past niet bij de route',
                'hint' => 'Hoe weet Laravel welk argument bij {post:slug} hoort?',
                'explanation' => 'Route model binding koppelt op naam: {post:slug} hoort bij een argument $post. $postSlug past daar niet bij, dus Laravel zoekt niets op en geeft een leeg Post-model mee. Elke pagina wordt dan een 404. Noem het argument $post.

```php
public function show(Post $post): View
```',
            ],
            [
                'line' => 12,
                'title' => 'Een controle die altijd slaagt',
                'hint' => 'Kan een post die route model binding gevonden heeft niet bestaan?',
                'explanation' => 'Bij een werkende binding komt het model uit de database, dus dan is exists altijd true. In deze code is exists juist altijd false, door het verkeerde argument. Vindt Laravel de slug niet, dan geeft de binding zelf al een 404. Wat je hier wel moet controleren, is of de post gepubliceerd is. Anders leest iedereen concepten en ingeplande posts via hun slug.

```php
abort_if($post->published_at === null || $post->published_at->isFuture(), 404);
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Posts en tags met een positie in de pivot'],
            [
                'topic_id' => $topics['Relaties'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use Illuminate\Database\Eloquent\Model;
                    use Illuminate\Database\Eloquent\Relations\BelongsTo;
                    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
                    use Illuminate\Database\Eloquent\Relations\HasMany;

                    class Post extends Model
                    {
                        public function author(): BelongsTo
                        {
                            return $this->belongsTo(User::class, 'user_id');
                        }

                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class);
                        }

                        public function tags(): BelongsToMany
                        {
                            return $this->belongsToMany(Tag::class)
                                ->withPivot('position')
                                ->withTimestamps()
                                ->orderByPivot('position');
                        }
                    }
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    namespace App\Models;

                    use Illuminate\Database\Eloquent\Model;
                    use Illuminate\Database\Eloquent\Relations\BelongsTo;
                    use Illuminate\Database\Eloquent\Relations\BelongsToMany;
                    use Illuminate\Database\Eloquent\Relations\HasMany;

                    class Post extends Model
                    {
                        public function comments(): HasMany
                        {
                            return $this->hasMany(Comment::class);
                        }

                        public function author(): BelongsTo
                        {
                            return $this->belongsTo(User::class, 'user_id');
                        }

                        public function tags(): BelongsToMany
                        {
                            return $this->belongsToMany(Tag::class, 'tag_post')
                                ->withPivot('position', 'created_at', 'updated_at')
                                ->orderBy('tags.position');
                        }
                    }
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 24,
                'title' => 'Verkeerde naam voor de pivot-tabel',
                'hint' => 'Hoe noemt Laravel de koppeltabel tussen posts en tags?',
                'explanation' => 'Laravel noemt een koppeltabel naar beide modellen in het enkelvoud, in alfabetische volgorde: post_tag. Zo heet de tabel ook, dus tag_post bestaat niet en elke query faalt. Laat het argument weg.

```php
return $this->belongsToMany(Tag::class)
```',
            ],
            [
                'line' => 25,
                'title' => 'Tijdstempels via withPivot',
                'hint' => 'Wie vult created_at in post_tag als je een tag koppelt?',
                'explanation' => 'withPivot() maakt kolommen alleen leesbaar via $tag->pivot. Bij attach() en sync() vult Laravel created_at en updated_at dan niet, dus die blijven leeg. withTimestamps() leest de kolommen en vult ze ook.

```php
->withPivot(\'position\')
->withTimestamps()
```',
            ],
            [
                'line' => 26,
                'title' => 'Sorteren op een kolom die er niet is',
                'hint' => 'In welke tabel staat de kolom position?',
                'explanation' => 'position hoort bij de koppeling, niet bij de tag, en staat daarom in post_tag. De kolom tags.position bestaat niet, dus de query geeft een databasefout. Met orderByPivot() sorteer je op een kolom van de koppeltabel.

```php
->orderByPivot(\'position\');
```',
            ],
        ]);

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Soft deletes voor posts'],
            [
                'topic_id' => $topics['Migrations'],
                'stars' => 3,
                'correct_code' => <<<'PHP'
                    <?php

                    use Illuminate\Database\Migrations\Migration;
                    use Illuminate\Database\Schema\Blueprint;
                    use Illuminate\Support\Facades\Schema;

                    return new class extends Migration
                    {
                        public function up(): void
                        {
                            Schema::table('posts', function (Blueprint $table) {
                                $table->softDeletes();
                                $table->string('deleted_reason')->nullable();
                            });
                        }

                        public function down(): void
                        {
                            Schema::table('posts', function (Blueprint $table) {
                                $table->dropSoftDeletes();
                                $table->dropColumn('deleted_reason');
                            });
                        }
                    };
                    PHP,
                'wrong_code' => <<<'PHP'
                    <?php

                    use Illuminate\Database\Migrations\Migration;
                    use Illuminate\Database\Schema\Blueprint;
                    use Illuminate\Support\Facades\Schema;

                    return new class extends Migration
                    {
                        public function up(): void
                        {
                            Schema::create('posts', function (Blueprint $table) {
                                $table->softDeletes('deleted_at');
                                $table->string('deleted_reason');
                            });
                        }

                        public function down(): void
                        {
                            Schema::dropIfExists('posts');
                        }
                    };
                    PHP,
                'published_at' => now(),
            ],
        );

        $exercise->errors()->delete();
        $exercise->errors()->createMany([
            [
                'line' => 11,
                'title' => 'Een tabel maken die al bestaat',
                'hint' => 'Bestaat de tabel posts al als deze migration draait?',
                'explanation' => 'Schema::create() maakt een nieuwe tabel, maar posts bestaat al. De migration faalt met de melding dat de tabel al bestaat. Kolommen toevoegen aan een bestaande tabel doe je met Schema::table().

```php
Schema::table(\'posts\', function (Blueprint $table) {
```',
            ],
            [
                'line' => 13,
                'title' => 'Verplichte kolom zonder waarde',
                'hint' => 'Welke reden heeft een post die niet verwijderd is?',
                'explanation' => 'Een post die niet verwijderd is, heeft geen reden. Zonder nullable() moet elke rij een waarde hebben. Bestaande posts krijgen dan een lege string, en een nieuwe post aanmaken faalt omdat de kolom geen standaardwaarde heeft.

```php
$table->string(\'deleted_reason\')->nullable();
```',
            ],
            [
                'line' => 19,
                'title' => 'Rollback verwijdert alle posts',
                'hint' => 'Wat is er van je posts over na php artisan migrate:rollback?',
                'explanation' => 'down() moet precies terugdraaien wat up() deed, en dat was twee kolommen toevoegen. dropIfExists(\'posts\') verwijdert de hele tabel met alle posts erin. Haal alleen de twee kolommen weg.

```php
Schema::table(\'posts\', function (Blueprint $table) {
    $table->dropSoftDeletes();
    $table->dropColumn(\'deleted_reason\');
});
```',
            ],
        ]);
    }
}
