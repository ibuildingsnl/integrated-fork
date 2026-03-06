# Diff report: `release/0.90` -> `improvement/24-final-changes`

## Scope

- Repository: `vendor/integrated/integrated`
- Compared range: `release/0.90...improvement/24-final-changes`
- Total: `676 files changed, 482544 insertions(+), 6677 deletions(-)`
- Commits in range: `281`
- This range is **stacked** and includes all previous `improvement/*` work up to and including `improvement/24-final-changes`.

## Commit stream on first-parent (high level milestones)

The list below captures the baseline stacked milestones through the `improvement/13-*` phase.
Later milestones and final branch updates are documented in sections **18-27**.

1. `46d659000` Merged in fix/menu-rendering (pull request #553)
2. `f6990a686` Merged in feature/article-links-search (pull request #515)
3. `c6f30b08f` Show selected image as first item in media library
4. `154f925ac` Implement Turbo into gallery
5. `e6feeeff2` Implement UX Turbo globally
6. `937e16109` allow removing channels if they have pages, remove the pages unlink content
7. `734d7630e` Improve removing channel functionality
8. `22fa73a24` Taxonomy Improvements
9. `46c8bdd09` Fix index with pagination
10. `03ed7257d` Content History Improvement
11. `8ee0a2997` Merge branch 'improvement/medialibrary' into improvement/content-history
12. `eaa378e0f` couple of extra changes for image editor
13. `23927c279` fix edit image
14. `354f3f16a` Merge branch 'release/0.90' into improvement/content-history
15. `9424c0a0a` Public files
16. `d92b7a089` Fix double declaration
17. `6b80bb4d8` Fixes for 0.90
18. `6f625443b` Merge branch 'improvement/website-bundle' into improvement/content-history
19. `60b4009ed` Fix image selection in editor
20. `78981aeba` Fix for Menu Builder
21. `f4ed01ada` Fix opening popup
22. `78b023d69` Some style changes
23. `8b945ceed` Cleanup and improvements
24. `a3863c77d` Add test for Menu Controller
25. `9678d7beb` Fix redirecting on clicking new block and add translations + fixes
26. `3ffaf6033` Normalize styling of the different components
27. `ab2acb795` Css opgeschoond
28. `39442d4be` Improved website builder + responsived toolbar
29. `c5a7f3947` public files
30. `b18085384` Multiple improvements and fixes for UX Turbo
31. `0dbb3f354` Switch to Pickr from Coloris
32. `f1b8e7249` Updated tailwind and some fixes
33. `658daa520` Allow linking to an author
34. `d35b16c38` Improve account management UX, bulk ops, and optional 2FA flows
35. `7c27aa04d` polish user admin UX, strengthen 2FA flow, and prevent content editor navigation regressions
36. `f5e8d632f` add controller tests for bulk actions and 2FA reset/deactivate flows
37. `d4f4fcf9f` Fixed not always being able to open library because of ux turbo
38. `3d567c7c5` Fixed parsing values and added parser test
39. `300d11500` Improvements and some other bug fixes
40. `0e5c21cc2` Don't break a page when you can show a default :)
41. `eb8c22a22` Add logging for failing indexer jobs
42. `3d123cca8` Fixes for MediaLibrary and custom sort
43. `6fdc69acc` Improved content history information display
44. `f47cebedb` No need for deadline to be required
45. `acd6d1f9c` Improvements for users
46. `a16b47066` optimize css delivery
47. `24271e742` Add caching and lazyload to mainly static items
48. `cadc4dcf8` created small endpoint to actively update notifications and indexer
49. `5bf81a044` reduced double functions
50. `a417414dc` Fixed locking refresh so it uses vanilla JS
51. `ad990b417` Wait for jQuery to load this.
52. `302891288` Fix media selection from TinyMCE editor
53. `21dbd72a5` Fix showAction being called from old ContentTypePage configs
54. `1d1214c08` Fix for Bulk Edit
55. `409bf4094` First set of improvements
56. `6db07ca99` overhaul bulk flow with capability-based actions, richer UI and stronger validation
57. `68beacf34` Bugfixes
58. `6269c43a1` resolve runtime regressions in channel create/edit/delete/update flows
59. `f1311618d` Fixes
60. `3f59fcd05` Improved SitemapBundle
61. `0b7933279` This change makes locking behavior safer and more robust in admin editing flows.
62. `2c2b3c93b` Improve content lock lifecycle and live navigator lock status
63. `63186c647` Merge branch 'improvement/11-bug-fixes' into improvement/13-locking-bundle

## Bundle/file-area impact (by changed file count)

- `src/Bundle/ContentBundle`: 269 files
- `src/Bundle/IntegratedBundle`: 104 files (mostly compiled frontend assets)
- `src/Bundle/UserBundle`: 61 files
- `src/Bundle/WebsiteBundle`: 30 files
- `src/Bundle/BlockBundle`: 30 files
- `src/Bundle/BrandBundle`: 25 files
- `src/Bundle/TaxonomyBundle`: 20 files
- `src/Bundle/FormTypeBundle`: 18 files
- `src/Bundle/ContentHistoryBundle`: 18 files
- `src/Bundle/WorkflowBundle`: 17 files
- `src/Bundle/PageBundle`: 16 files
- `src/Bundle/ChannelBundle`: 13 files
- `src/Bundle/SitemapBundle`: 9 files
- `src/Bundle/SolrBundle`: 8 files
- `src/Bundle/LockingBundle`: 5 files
- `src/Bundle/InstallerBundle`: 5 files
- `src/Bundle/MenuBundle`: 4 files
- `src/Bundle/ImageBundle`: 2 files
- plus shared files (`package.json`, `tailwind.config.js`, `webpack.config.js`, compiled assets in `IntegratedBundle`, tests, and docs)

## What changed and why it was needed

## 1) UX Turbo rollout and navigation consistency

- Changed across content, media, block, channel, form templates, JS, and routing.
- Added/updated turbo stream templates such as:
  - `src/Bundle/ContentBundle/Resources/views/content/edit.turbo_stream.html.twig`
  - `src/Bundle/ContentBundle/Resources/views/content/edit.iframe.turbo_stream.html.twig`
  - `src/Bundle/ContentBundle/Resources/views/content/flash.turbo_stream.html.twig`
  - `src/Bundle/ContentBundle/Resources/views/channel/delete.turbo_stream.html.twig`
- Updated client scripts (`turbo_navigation.js`, `edit.js`, `global.js`, `comments.js`, media scripts) for Turbo lifecycle and event rebinding.
- Why needed:
  - Existing full-page navigation caused reload/flicker and stale UI bindings.
  - Turbo navigation needed deterministic behavior in edit flows, media dialogs, and dropdowns.
  - Multiple regressions were fixed where Turbo replaced DOM but JS listeners were not rebound.

## 2) Bulk edit redesign (select/configure/confirm flow)

- Major additions in `src/Bundle/ContentBundle/Bulk/*`:
  - capability resolver and option matcher
  - dedicated handlers and form providers for `featured`, `premium`, `publish window`, `canonical`, relation actions
  - stronger factories and action gating by content capabilities
- New bulk action documents and ODM mappings:
  - `Document/Bulk/Action/CanonicalAction.php`
  - `Document/Bulk/Action/FeaturedAction.php`
  - `Document/Bulk/Action/PremiumAction.php`
  - `Document/Bulk/Action/PublishWindowAction.php`
  - mapping files under `Resources/config/doctrine/Bulk.Action.*.mongodb.xml`
- New/updated form types:
  - `BulkActionCanonicalType`, `BulkActionFeaturedType`, `BulkActionPremiumType`, `BulkActionPublishWindowType`
  - improved relation type handling and validation
- UI rewrite in Twig + JS:
  - `Resources/views/bulk/configure.html.twig`
  - `Resources/views/bulk/confirm.html.twig`
  - `Resources/views/bulk/select.html.twig`
  - `Resources/public/js/bulk_publish_window.js`
  - `Resources/public/js/bulk_taxonomy_category.js`
  - relation and taxonomy widgets aligned with editor behavior
- Why needed:
  - Previous flow mixed incompatible actions for content without required fields/workflow.
  - Missing capability checks caused invalid operations and 422 errors.
  - Users needed editor-like controls (yes/no sliders, taxonomy popup style, source/source URL, publication window usability).
  - Confirm step and persistence behavior needed to stay coherent across Turbo navigation.

## 3) Bulk edit validation and Solr/filter correctness fixes

- Adjusted provider/query logic in:
  - `src/Bundle/ContentBundle/Provider/ContentProvider.php`
  - `src/Bundle/ContentBundle/Solr/Query/Type/Content.php`
  - `src/Bundle/ContentBundle/Solr/Query/Type/IntegratedContentBlock.php`
  - `src/Bundle/ContentBundle/Provider/SolariumProvider.php`
- Added tests:
  - `Tests/Solr/Query/Type/ContentOptionsTest.php`
  - `Tests/Solr/Query/Type/IntegratedContentBlockOptionsTest.php`
- Why needed:
  - Prevent malformed facet queries (e.g. empty facet lists) and non-scalar input crashes.
  - Fix date range handling (including reversed input) so filters include full intended day boundaries.
  - Stabilize bulk select query updates instead of stepping pager when applying filters.

## 4) Locking reliability and live lock visibility

- `src/Bundle/LockingBundle/Controller/ApiController.php`
- `src/Bundle/LockingBundle/Resources/config/routing.xml`
- `src/Bundle/LockingBundle/Resources/views/locking.refresh.html.twig`
- `src/Common/Locks/Provider/DBAL/Manager.php`
- Content navigator integration:
  - `src/Bundle/ContentBundle/Resources/views/content/index.html.twig`
  - `src/Bundle/ContentBundle/Controller/ContentController.php`
  - `src/Bundle/ContentBundle/Resources/config/routing/content.xml`
- Tests:
  - `src/Bundle/LockingBundle/Tests/Controller/ApiControllerTest.php`
  - `src/Bundle/LockingBundle/Tests/Resources/LockingAssetsTest.php`
  - `src/Bundle/ContentBundle/Tests/Resources/ContentNavigatorTemplateTest.php`
- Why needed:
  - Make lock lifecycle safer in edit sessions and prevent stale/misleading lock states.
  - Replace brittle jQuery-dependent lock refresh with robust JS behavior.
  - Add live lock polling in navigator while preserving current state on polling failures.
  - Reduce lock cleanup risk and align timeout/refresh expectations with real usage.

## 5) Top bar/indexer/notifications fragment behavior

- Affected mainly in:
  - `src/Bundle/ContentBundle/Resources/views/content/navdropdowns.html.twig`
  - `src/Bundle/ContentBundle/EventListener/FragmentCacheInvalidationSubscriber.php`
  - related controller/template wiring in content base/header
- Why needed:
  - Reduce blocking header rendering cost and avoid full-page fragment embedding mistakes.
  - Keep dynamic indicators (notifications/indexer/assignments) up to date with less manual reload.
  - Provide clearer async behavior while avoiding visible layout jumps where possible.

## 6) Channel management hard fixes (runtime regressions)

- `src/Bundle/ContentBundle/Controller/ChannelController.php`
- `src/Bundle/ContentBundle/Resources/config/routing/channel.xml`
- channel Twig templates for create/edit/delete flow
- Why needed:
  - Fix runtime-breaking issues in create/edit/delete/update:
    - wrong event constant usage after aliasing
    - invalid return type declaration for delete form
    - route pointing to non-existing controller action
  - Restore channel CRUD reliability after Turbo/routing changes.

## 7) SitemapBundle upgrade

- Controllers:
  - `src/Bundle/SitemapBundle/Controller/DefaultController.php`
  - `src/Bundle/SitemapBundle/Controller/NewsController.php`
  - `src/Bundle/SitemapBundle/Controller/RobotsController.php`
- Routing and templates:
  - `src/Bundle/SitemapBundle/Resources/config/routing.xml`
  - `src/Bundle/SitemapBundle/Resources/views/default/index.xml.twig`
  - `src/Bundle/SitemapBundle/Resources/views/default/list.xml.twig`
  - `src/Bundle/SitemapBundle/Resources/views/news/index.xml.twig`
- Tests:
  - `src/Bundle/SitemapBundle/Tests/Resources/SitemapRoutingTest.php`
  - `src/Bundle/SitemapBundle/Tests/Resources/SitemapTemplateTest.php`
- Why needed:
  - Improve sitemap endpoint structure, routing clarity, and XML output robustness.
  - Fix page sitemap errors and ensure content-type coverage is explicit and testable.

## 8) Media library/editor integration fixes

- Major media/controller/template/JS updates:
  - `src/Bundle/ContentBundle/Controller/MediaController.php`
  - `src/Bundle/ContentBundle/Resources/assets/js/mediaGallery.js`
  - `src/Bundle/ContentBundle/Resources/assets/js/mediagallery_selection.js`
  - `src/Bundle/ContentBundle/Resources/views/media/*`
  - `src/Bundle/FormTypeBundle/Resources/assets/js/tinymce-integrated-browser/ui/dialog.js`
- Why needed:
  - Fix issues where media modal/iframe flow stopped responding under Turbo.
  - Fix insert behavior where image tags were inserted with invalid/empty `src`.
  - Ensure close actions and selection behavior work reliably in TinyMCE integrations.

## 9) Image handling robustness

- `src/Bundle/ImageBundle/Twig/Extension/ImageExtension.php`
- `src/Bundle/ImageBundle/Twig/Extension/GregwarImageExtension.php`
- `src/Bundle/ContentBundle/EventListener/ContentFeaturedImageListener.php`
- `src/Bundle/ContentBundle/Form/DataTransformer/ImageTransformer.php`
- Why needed:
  - Prevent front-end breakage when image input path variants (`/storage` vs `/files`) are resolved.
  - Make image rendering more fault-tolerant with controlled fallback behavior.
  - Address cases where featured images existed but were not rendered correctly by handler path resolution.

## 10) Content history improvements

- Large updates in:
  - `src/Bundle/ContentHistoryBundle/Controller/ContentHistoryController.php`
  - `src/Bundle/ContentHistoryBundle/History/Parser.php`
  - `src/Bundle/ContentHistoryBundle/Diff/ArrayComparer.php`
  - `src/Bundle/ContentHistoryBundle/EventListener/ContentHistorySubscriber.php`
  - views under `Resources/views/content_history/*`
  - tests under `Tests/*`
- Why needed:
  - Improve readability and structure of history diffs in admin.
  - Capture request context better and stabilize parse/diff edge cases.
  - Add tests to avoid regressions in history formatting and event capture.

## 11) User/admin workflow improvements

- Controllers/forms/views/services in `src/Bundle/UserBundle/*`
- Added `BulkUserActionService` + tests.
- Updated 2FA/profile/user management routes and UI.
- Why needed:
  - Improve account management UX and bulk user operations.
  - Make optional 2FA flows and reset/deactivate behavior safer and better tested.
  - Prevent regressions in user admin operations after broader UI/Turbo changes.

## 12) Brand/channel link integrity

- Added command + test:
  - `src/Bundle/BrandBundle/Command/CleanupBrandChannelLinksCommand.php`
  - `src/Bundle/BrandBundle/Tests/Command/CleanupBrandChannelLinksCommandTest.php`
- Updated Brand document/mappings and related listeners/JS/templates.
- Why needed:
  - Clean invalid/null brand-channel links safely.
  - Prevent stale linkage from breaking channel-related views and filters.
  - Harden command behavior and test coverage around cleanup scenarios.

## 13) Article search and linking enhancements

- Added new controller + Vue UI:
  - `src/Bundle/ContentBundle/Controller/ArticleSearchController.php`
  - `src/Bundle/ContentBundle/Resources/assets/js/article_search.js`
  - Vue components under `Resources/assets/js/vue/article-search/*`
  - route config `Resources/config/routing/article_search.xml`
  - Twig view `Resources/views/article_search/article_search.html.twig`
- Why needed:
  - Improve editor linking workflow with better search UX/status feedback.
  - Reduce incorrect links and improve internal linking productivity.

## 14) Website/menu/block/taxonomy quality improvements

- Website/menu:
  - `src/Bundle/WebsiteBundle/Controller/MenuController.php`
  - `src/Bundle/WebsiteBundle/Twig/Extension/MenuExtension.php`
  - toolbar/menu theme templates and tests
- Block bundle:
  - block CRUD templates/controllers/JS improvements for iframe/Turbo compatibility
- Taxonomy:
  - controller/service/view updates for index/list/viewer/indexer behavior
- Why needed:
  - Fix rendering and navigation issues in website builder/menu builder.
  - Improve editor consistency and reduce UI friction in block and taxonomy workflows.

## 15) Workflow/indexer and queue diagnostics

- `src/Bundle/SolrBundle/Command/IndexerQueueCommand.php`
- `src/Common/Queue/Provider/DBAL/QueueProvider.php`
- `src/Bundle/WorkflowBundle/Solr/Query/WorkflowExtension.php`
- Why needed:
  - Improve visibility/logging around failing indexer jobs.
  - Keep queue and workflow-based filtering behavior explicit and testable.

## 16) Build and frontend asset pipeline updates

- Updated:
  - `package.json`
  - `yarn.lock`
  - `webpack.config.js`
  - `postcss.config.js`
  - `tailwind.config.js`
- Regenerated compiled assets under:
  - `src/Bundle/IntegratedBundle/Resources/public/*`
- Added/new bundles:
  - `article-search.js/.css`, `pickr.js/.css`, iconoir assets, rebuilt entrypoints/manifest
- Why needed:
  - Source JS/SCSS changes required rebuild of distributed bundle assets.
  - Move to updated frontend tooling/components (e.g. Pickr) and keep compiled output in sync.
  - Keep production assets aligned with template/script expectations.

## 17) Tests added/extended across changed domains

- New or expanded tests in:
  - Content bulk, forms, solr options
  - Locking API/assets and content navigator lock rendering
  - Sitemap routing/templates
  - User bulk/profile/2FA and form behavior
  - Brand cleanup command
  - Content history parser/subscriber/controller
  - Menu controller
- Why needed:
  - The branch introduced broad behavior changes; coverage was necessary to reduce regression risk.
  - Several fixes were specifically prompted by runtime regressions and needed guardrails.

## 18) Workflow and publication state hardening

- Added/fixed behavior in workflow change-state flow and publication sync:
  - prevent invalid transitions where concept content remained publishable
  - turbo refresh improvements for publication panels
  - extra tests around workflow state updates and queue-triggered propagation
- Why needed:
  - Workflow/UI state mismatches created invalid publication records and confusing editor status.
  - Publication list behavior under Turbo needed deterministic refresh and guardrails.

## 19) Page and block bundle improvements

- Page admin:
  - improved draft visibility and filtering behavior
  - orphan-channel management and safer page/channel filtering
- Block bundle:
  - block used-by parsing hardening and faster usage lookups via denormalized `blockIds`
  - improved block create/edit/remove flows and used-by accessibility for edit-capable users
  - container/grid behavior cleanup and facet filter hardening
- Why needed:
  - Reduce slow/fragile block usage queries and improve editor confidence when cleaning up blocks.
  - Make page administration safer when channels are removed or page state is mixed.

## 20) Brand and channel connector hardening

- Brand/channel management:
  - stronger ownership and return-flow checks
  - channel create/edit safeguards for website-only fields
  - OAuth/session flow hardening and safer connector redirects
  - connector action UX cleanup (availability-based actions, reset styles, post-create flow)
- Why needed:
  - Connector configuration had edge cases that caused broken redirects or mismatched field sets.
  - Brand/channel linking needed stricter invariants to prevent invalid cross-brand links.

## 21) Taxonomy reliability and ordering

- Improved taxonomy create/edit/index behavior:
  - parent/ordering consistency fixes
  - pagination normalization for ODM paginator results
  - indexing hardening and post-create behavior cleanup
  - delete guard improvements and relation select label correctness
- Why needed:
  - Taxonomy ordering and refresh behavior were inconsistent under Turbo and mixed datasets.
  - Deletion/indexing needed safer checks to avoid partial state and stale UI.

## 22) Editor/picker/sidebar UX stabilization

- Sidebar/menu:
  - persisted open-state and collapse behavior across admin navigation
  - content-default-open logic and order stabilization
- Pickers/forms:
  - Pickr asset loading and popup stability fixes
  - relation picker/typeahead initialization and optional relation value handling
  - website-aware search-selection controls + translations and classification helpers
- Why needed:
  - UI state reset/regression issues in Turbo sessions caused repeated user friction.
  - Picker initialization timing and asset order caused broken interactions on first load.

## 23) Flash/message and admin interaction consistency

- Unified flash stack behavior for Turbo and full-page requests:
  - stable flash container position
  - correct routing of frame flashes to global flash area
  - reduced duplicate/stacking inconsistencies
- Why needed:
  - Mixed rendering contexts created hidden, duplicate, or misplaced notifications.
  - Consistent feedback is required for trust in save/publish/editor actions.

## 24) User, roles, and group management upgrades

- User/auth improvements:
  - identifier login support (username or email)
  - stronger role handling to prevent non-admin privilege escalation
  - backward compatibility for legacy choice-type wiring
- Group management:
  - user membership management directly from group detail
  - visual polish and usability updates for group user administration
- Why needed:
  - Authentication/authorization flows needed stricter safety and clearer UX.
  - Group maintenance was previously slow and error-prone in larger organizations.

## 25) Website/grid/editor final fixes in `improvement/24-final-changes`

- Final branch fixes include:
  - remove publication popup in editor
  - show missing block warning in editor grid
  - make grid template resolution request-scoped (fix cross-request theme/template leakage)
  - queue and lock error-policy hardening (explicit failures + retry/reschedule semantics)
  - deterministic tests for lock/flusher/queue behavior
- Why needed:
  - Shared service-state caused domain/theme cross-contamination in grid rendering.
  - Queue/lock paths needed explicit failure semantics to avoid silent data integrity issues.

## 26) Additional frontend/theme consistency updates

- Icon and style updates:
  - iconoir upgrade and broader icon usage consistency
  - settings-group navigation style additions
  - connector/action card and button alignment cleanups
- Why needed:
  - Ensure coherent cross-bundle admin styling while preserving compatibility in older templates.

## 27) Full range coverage note

- The report covers all work from:
  - media/turbo foundation and early UX rollouts
  - bulk-edit/workflow/taxonomy/locking hardening phases
  - brand/channel/page/block/user/auth follow-up bundles
  - final queue/lock/grid correctness and regression-test updates
- For exact commit-by-commit trace, use the first-parent log command in the reproduce section.

## Notes on “why this was needed” at branch level

- The stack addresses a long chain of real admin pain points:
  - Turbo adoption regressions
  - bulk edit capability mismatch and validation failures
  - media insert and editor interoperability bugs
  - lock lifecycle and stale lock UI problems
  - sitemap endpoint failures
  - channel CRUD runtime breaks
  - image path resolution/fallback failures
- The very large compiled asset diff is expected because many source frontend modules were changed and recompiled.

## Reproduce this report locally

```bash
cd vendor/integrated/integrated
git diff --shortstat release/0.90...improvement/24-final-changes
git diff --name-status release/0.90...improvement/24-final-changes
git log --first-parent --oneline release/0.90..improvement/24-final-changes
```
