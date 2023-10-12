import { startStimulusApp } from '@symfony/stimulus-bridge';
export let application = startStimulusApp(require.context(
    '@symfony/stimulus-bridge/lazy-controller-loader!./controllers',
    true,
    /\.[jt]sx?$/
));
