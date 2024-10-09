import { AppRouter } from './routes';
import ThemeProvider from './theme';

export default function App() {
  return (
    <ThemeProvider>
      <AppRouter />
    </ThemeProvider>
  );
}
