import { Application } from '@hotwired/stimulus';
import LiveController from '@symfony/ux-live-component';

if (!window.integratedArticleSearchStimulus) {
    const application = Application.start();
    application.register('live', LiveController);
    window.integratedArticleSearchStimulus = application;
}
