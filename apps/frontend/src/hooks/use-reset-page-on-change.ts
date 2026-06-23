import { Dispatch, SetStateAction, useState } from "react";
import { PaginationState } from "@tanstack/react-table";

/**
 * Resets `pagination.pageIndex` to 0 whenever any value in `filterDeps` changes,
 * without using an effect (avoids the cascading-render anti-pattern flagged by
 * react-hooks/set-state-in-effect — see https://react.dev/learn/you-might-not-need-an-effect)
 * and without reading/writing a ref during render (avoids react-hooks/refs, part of this
 * project's React Compiler lint rules).
 *
 * Implements React's documented "adjusting state when a prop changes" pattern using
 * `useState` (not `useRef`) to track the previous deps, calling `setState` directly during
 * render. React detects the state update and re-renders immediately before committing to the
 * screen, so this is synchronous from the user's perspective — unlike a `useEffect`, which
 * would commit once with stale state and then trigger a second, visible cascading render.
 *
 * Call this on every render, unconditionally, alongside the `useState<PaginationState>`
 * declaration it resets.
 */
export function useResetPageOnFilterChange(
  pagination: PaginationState,
  setPagination: Dispatch<SetStateAction<PaginationState>>,
  filterDeps: readonly unknown[]
): void {
  const [prevDeps, setPrevDeps] = useState(filterDeps);

  const changed =
    prevDeps.length !== filterDeps.length ||
    prevDeps.some((dep, i) => dep !== filterDeps[i]);

  if (changed) {
    setPrevDeps(filterDeps);
    if (pagination.pageIndex !== 0) {
      setPagination((p) => ({ ...p, pageIndex: 0 }));
    }
  }
}
