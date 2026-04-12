export class EventDispatcher {
    constructor(provider) {
        this.provider = provider;
    }

    async dispatch(eventName, event) {
        return new Promise(async (resolve, reject) => {

            event.stopPropagation = false;

            const originalResolve = event.resolve;
            const originalReject = event.reject;

            event.resolve = (...args) => {
                if (originalResolve) originalResolve(...args);
            };

            event.reject = (...args) => {
                event.stopPropagation = true;
                if (originalReject) originalReject(...args);
                reject(...args);
            };

            const listeners = this.provider.getListeners(eventName);

            for (const listener of listeners) {

                try {
                    const result = listener.handle(event);

                    if (result && typeof result.then === 'function') {
                        await result;
                    }

                } catch (err) {
                    event.reject(err);
                    return;
                }

                if (event.stopPropagation) {
                    return;
                }
            }

            resolve();
        });
    }
}
