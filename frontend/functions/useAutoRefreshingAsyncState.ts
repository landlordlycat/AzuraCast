import useRefreshableAsyncState from "~/functions/useRefreshableAsyncState.ts";
import {Pausable, UseAsyncStateOptions, UseAsyncStateReturn, useIntervalFn} from "@vueuse/core";
import {computed} from "vue";

interface AutoRefreshingAsyncStateOptions<Shallow extends boolean, D = any>
    extends UseAsyncStateOptions<Shallow, D> {
    timeout?: number
}

interface AutoRefreshingAsyncStateReturn<Data, Params extends any[], Shallow extends boolean>
    extends UseAsyncStateReturn<Data, Params, Shallow>, Pausable {
}

export default function useAutoRefreshingAsyncState<Data, Params extends any[] = [], Shallow extends boolean = true>(
    promise: Promise<Data> | ((...args: Params) => Promise<Data>),
    initialState: Data,
    options: AutoRefreshingAsyncStateOptions<Shallow, Data> = {}
): AutoRefreshingAsyncStateReturn<Data, Params, Shallow> {
    const {
        timeout = 15000
    } = options ?? {}

    const asyncStateReturn = useRefreshableAsyncState(
        promise,
        initialState,
        {
            throwError: true,
            ...options
        }
    );

    const intervalDelay = computed(() =>
        (!document.hidden) ? timeout : (timeout * 2)
    );

    const intervalFnReturn = useIntervalFn(
        async () => {
            try {
                // @ts-expect-error Function call is accurate.
                await asyncStateReturn.execute();
            } catch {
                intervalFnReturn.pause();
            }
        },
        intervalDelay
    );

    return {
        ...asyncStateReturn,
        ...intervalFnReturn
    };
}
