import {Application} from '@hotwired/stimulus';
import {definitionForModuleAndIdentifier, identifierForContextKey} from '@hotwired/stimulus-webpack-helpers';

// Start Stimulus
const application = Application.start();
application.debug = process.env.NODE_ENV === 'development';

// Auto‑register all controllers in ./controllers
const context = require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /_controller\.[jt]sx?$/
);

application.load(
    context.keys()
        .map((key) => {
            const identifier = identifierForContextKey(key);
            if (!identifier) {
                return null;
            }

            return definitionForModuleAndIdentifier(
                context(key),
                `${identifier}` // without a prefix
            );
        })
        .filter(Boolean)
);
