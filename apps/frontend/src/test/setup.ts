// Vitest + jsdom global setup.
// Testing Library's matchers (toBeInTheDocument, etc.) are intentionally not
// added here since @testing-library/jest-dom is outside this phase's
// dependency list (see Step C1) -- assertions use plain DOM queries instead.
import { afterEach } from "vitest";
import { cleanup } from "@testing-library/react";

afterEach(() => {
  cleanup();
});
